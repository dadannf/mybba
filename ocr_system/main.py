"""
=============================================
FastAPI Main Application
OCR System REST API
=============================================
"""

from fastapi import FastAPI, File, UploadFile, Form, Depends, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from sqlalchemy.orm import Session
from loguru import logger
from datetime import datetime
import os
import shutil
import json
from typing import Optional

from app.core.config import settings
from app.core.database import get_db, OCRValidation, init_db
from app.models.schemas import *
from app.services.ocr_service import get_ocr_service
from app.services.validation_service import get_validation_service
from app.utils.preprocessing import ImagePreprocessor
from app.utils.text_parser import get_parser

# Initialize FastAPI app
app = FastAPI(
    title="Bank Transfer OCR Validation System",
    description="Deep Learning OCR untuk validasi bukti transfer bank Indonesia",
    version="1.0.0",
    debug=settings.debug
)

# CORS middleware - Allow all origins for ngrok/development
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Allow all origins for ngrok support
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Initialize services
preprocessor = ImagePreprocessor()
ocr_service = get_ocr_service()
parser = get_parser()
validation_service = get_validation_service()

# Setup logging
logger.add(
    settings.log_file,
    rotation=settings.log_rotation,
    retention=settings.log_retention,
    level=settings.log_level
)


@app.on_event("startup")
async def startup_event():
    """Initialize database on startup"""
    logger.info("Starting OCR System API...")
    init_db()
    logger.info("OCR System API started successfully")


@app.get("/", response_model=HealthCheckResponse)
async def root():
    """Health check endpoint"""
    return {
        "status": "running",
        "timestamp": datetime.now(),
        "version": "1.0.0",
        "ocr_ready": True,
        "database_connected": True
    }


@app.post("/api/v1/validate-transfer", response_model=ValidateTransferResponse)
async def validate_transfer(
    file: UploadFile = File(...),
    uploader_type: str = Form(...),
    uploader_id: str = Form(...),
    expected_amount: float = Form(...),
    expected_nis: str = Form(...),
    expected_nama: str = Form(...),
    keuangan_id: Optional[int] = Form(None),
    db: Session = Depends(get_db)
):
    """
    Main endpoint untuk validasi bukti transfer
    
    Steps:
    1. Save uploaded file
    2. Preprocessing
    3. OCR (detection + recognition)
    4. Parse information
    5. Validate
    6. Save to database
    7. Return result
    """
    start_time = datetime.now()
    
    try:
        # Validate file type
        if not file.filename.lower().endswith(tuple(settings.allowed_extensions_list)):
            raise HTTPException(
                status_code=400,
                detail=f"File type not allowed. Allowed: {settings.allowed_extensions}"
            )
        
        # Save uploaded file
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"{uploader_type}_{uploader_id}_{timestamp}_{file.filename}"
        file_path = os.path.join(settings.upload_dir, filename)
        
        os.makedirs(settings.upload_dir, exist_ok=True)
        
        with open(file_path, "wb") as buffer:
            shutil.copyfileobj(file.file, buffer)
        
        logger.info(f"File saved: {file_path}")
        
        # Step 1: Preprocessing (try gentle approach first)
        logger.info("Step 1: Preprocessing...")
        preprocess_result = preprocessor.preprocess(
            file_path,
            apply_denoise=False,  # Disable denoise - can remove text
            apply_contrast=True,   # Keep contrast enhancement
            apply_rotation=False,  # Disable rotation - can cause issues
            apply_shadow_removal=False  # Skip shadow removal for speed
        )
        processed_image = preprocess_result['processed']
        
        # Step 2: OCR on preprocessed image
        logger.info("Step 2: OCR Detection & Recognition (preprocessed)...")
        ocr_result = ocr_service.detect_and_recognize(processed_image)
        
        # Fallback: Try original image if preprocessed failed
        if not ocr_result['results']:
            logger.warning("⚠️ No text detected in preprocessed image, trying original...")
            original_image = preprocess_result['original']
            # Resize only
            original_resized = preprocessor.resize_image(original_image)
            ocr_result = ocr_service.detect_and_recognize(original_resized)
        
        if not ocr_result['results']:
            logger.error("❌ No text detected in both preprocessed and original image")
            
            # Use error message from OCR service if available (includes quality validation)
            if 'error' in ocr_result and ocr_result['error']:
                error_detail = ocr_result['error']
            else:
                error_detail = "No text detected in image. "
                error_detail += "Please ensure: "
                error_detail += "1) Clear, high-resolution image (min 1000x1000px). "
                error_detail += "2) Good lighting and sharp focus. "
                error_detail += "3) Text readable by human eye. "
                error_detail += "4) Not blurry or heavily compressed."
            
            raise HTTPException(
                status_code=400,
                detail=error_detail
            )
        
        # Step 3: Parse
        logger.info("Step 3: Parsing information...")
        parsed_data = parser.parse(ocr_result)
        
        # Step 4: Validate
        logger.info("Step 4: Validating...")
        validation_result = validation_service.validate(
            ocr_result=ocr_result,
            parsed_data=parsed_data,
            expected_amount=expected_amount,
            expected_nis=expected_nis,
            expected_nama=expected_nama
        )
        
        # Step 5: Save to database
        logger.info("Step 5: Saving to database...")
        ocr_validation = OCRValidation(
            filename=filename,
            file_path=file_path,
            uploader_type=uploader_type,
            uploader_id=uploader_id,
            raw_text=ocr_result.get('raw_text', ''),
            detected_boxes=json.dumps([r['bbox'] for r in ocr_result['results']]),
            bank_name=parsed_data.get('bank_name'),
            account_number=parsed_data.get('account_number'),
            account_name=parsed_data.get('account_name'),
            transfer_amount=parsed_data.get('transfer_amount'),
            transfer_date=parsed_data.get('transfer_date'),
            reference_number=parsed_data.get('reference_number'),
            expected_amount=expected_amount,
            expected_nis=expected_nis,
            expected_nama=expected_nama,
            keuangan_id=keuangan_id,
            overall_confidence=ocr_result.get('overall_confidence', 0.0),
            detection_confidence=ocr_result.get('detection_confidence', 0.0),
            recognition_confidence=ocr_result.get('recognition_confidence', 0.0),
            amount_match_score=validation_result['confidence_scores']['amount_match'],
            validation_status='pending',
            auto_decision=validation_result['decision'],
            validation_message=validation_result['decision_reason'],
            processing_time_ms=(datetime.now() - start_time).total_seconds() * 1000
        )
        
        db.add(ocr_validation)
        db.commit()
        db.refresh(ocr_validation)
        
        logger.info(f"Validation saved with ID: {ocr_validation.id}")
        
        # Prepare response
        response_data = ValidationResultResponse(
            validation_id=ocr_validation.id,
            decision=validation_result['decision'],
            validation_score=validation_result['validation_score'],
            validation_status=ValidationStatus.PENDING,
            confidence_scores=ConfidenceScores(**validation_result['confidence_scores']),
            validation_checks=ValidationChecks(**validation_result['validation_checks']),
            decision_reason=validation_result['decision_reason'],
            requires_manual_review=validation_result['requires_manual_review'],
            ocr_result=OCRResultResponse(
                raw_text=ocr_result['raw_text'],
                detected_regions=ocr_result['num_regions'],
                detection_confidence=ocr_result['detection_confidence'],
                recognition_confidence=ocr_result['recognition_confidence'],
                overall_confidence=ocr_result['overall_confidence'],
                processing_time_ms=ocr_result['processing_time_ms']
            ),
            parsed_data=ParsedDataResponse(**parsed_data),
            processing_time_ms=ocr_validation.processing_time_ms,
            created_at=ocr_validation.created_at
        )
        
        return ValidateTransferResponse(
            success=True,
            message="Validation completed successfully",
            data=response_data
        )
        
    except HTTPException as he:
        raise he
    except Exception as e:
        logger.error(f"Validation error: {e}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/v1/validations", response_model=ValidationListResponse)
async def get_validations(
    uploader_id: Optional[str] = None,
    status: Optional[str] = None,
    limit: int = 50,
    offset: int = 0,
    db: Session = Depends(get_db)
):
    """Get list of validations"""
    query = db.query(OCRValidation)
    
    if uploader_id:
        query = query.filter(OCRValidation.uploader_id == uploader_id)
    
    if status:
        query = query.filter(OCRValidation.validation_status == status)
    
    total = query.count()
    validations = query.order_by(OCRValidation.created_at.desc()).limit(limit).offset(offset).all()
    
    return ValidationListResponse(
        success=True,
        total=total,
        data=[ValidationListItem.from_orm(v) for v in validations]
    )


@app.get("/api/v1/validations/{validation_id}", response_model=ValidationDetailResponse)
async def get_validation_detail(validation_id: int, db: Session = Depends(get_db)):
    """Get detail of specific validation"""
    validation = db.query(OCRValidation).filter(OCRValidation.id == validation_id).first()
    
    if not validation:
        return ValidationDetailResponse(
            success=False,
            error="Validation not found"
        )
    
    return ValidationDetailResponse(
        success=True,
        data={
            'id': validation.id,
            'filename': validation.filename,
            'file_path': validation.file_path,
            'uploader_type': validation.uploader_type,
            'uploader_id': validation.uploader_id,
            'raw_text': validation.raw_text,
            'bank_name': validation.bank_name,
            'account_number': validation.account_number,
            'account_name': validation.account_name,
            'transfer_amount': validation.transfer_amount,
            'transfer_date': validation.transfer_date.isoformat() if validation.transfer_date else None,
            'reference_number': validation.reference_number,
            'expected_amount': validation.expected_amount,
            'expected_nis': validation.expected_nis,
            'expected_nama': validation.expected_nama,
            'overall_confidence': validation.overall_confidence,
            'amount_match_score': validation.amount_match_score,
            'validation_status': validation.validation_status,
            'auto_decision': validation.auto_decision,
            'validation_message': validation.validation_message,
            'processing_time_ms': validation.processing_time_ms,
            'created_at': validation.created_at.isoformat(),
        }
    )


@app.post("/api/v1/validations/{validation_id}/approve", response_model=ManualApprovalResponse)
async def manual_approval(
    validation_id: int,
    request: ManualApprovalRequest,
    db: Session = Depends(get_db)
):
    """Manual approval/rejection by admin"""
    validation = db.query(OCRValidation).filter(OCRValidation.id == validation_id).first()
    
    if not validation:
        raise HTTPException(status_code=404, detail="Validation not found")
    
    validation.validation_status = request.status.value
    validation.validated_by = request.validated_by
    validation.validated_at = datetime.now()
    
    if request.notes:
        validation.validation_message = request.notes
    
    db.commit()
    
    return ManualApprovalResponse(
        success=True,
        message=f"Validation {request.status.value} successfully",
        validation_id=validation_id
    )


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "main:app",
        host=settings.api_host,
        port=settings.api_port,
        reload=settings.debug,
        workers=1 if settings.debug else settings.api_workers
    )
