"""
=============================================
Pydantic Models for API Request/Response
=============================================
"""

from pydantic import BaseModel, Field, validator
from typing import Optional, Dict, List
from datetime import datetime
from enum import Enum


class UploaderType(str, Enum):
    """Type of uploader"""
    ADMIN = "admin"
    SISWA = "siswa"


class ValidationDecision(str, Enum):
    """Validation decision"""
    ACCEPT = "accept"
    REJECT = "reject"
    REVIEW = "review"


class ValidationStatus(str, Enum):
    """Validation status"""
    PENDING = "pending"
    APPROVED = "approved"
    REJECTED = "rejected"


class UploadRequest(BaseModel):
    """Request untuk upload bukti transfer"""
    uploader_type: UploaderType
    uploader_id: str = Field(..., description="Username admin atau NIS siswa")
    expected_amount: float = Field(..., gt=0, description="Nominal yang diharapkan")
    expected_nis: str = Field(..., min_length=1, description="NIS siswa")
    expected_nama: str = Field(..., min_length=1, description="Nama siswa")
    keuangan_id: Optional[int] = Field(None, description="ID keuangan terkait")


class OCRResultResponse(BaseModel):
    """Response OCR result"""
    raw_text: str
    detected_regions: int
    detection_confidence: float
    recognition_confidence: float
    overall_confidence: float
    processing_time_ms: float


class ParsedDataResponse(BaseModel):
    """Response parsed data"""
    bank_name: Optional[str] = None
    account_number: Optional[str] = None
    account_name: Optional[str] = None
    transfer_amount: Optional[float] = None
    transfer_date: Optional[datetime] = None
    reference_number: Optional[str] = None


class ConfidenceScores(BaseModel):
    """Confidence scores"""
    overall_ocr: float
    detection: float
    recognition: float
    amount_match: float
    name_match: float


class ValidationChecks(BaseModel):
    """Validation checks"""
    ocr_quality: bool
    amount_valid: bool
    name_valid: bool
    bank_detected: bool
    date_detected: bool


class ValidationResultResponse(BaseModel):
    """Response hasil validasi"""
    validation_id: int
    decision: ValidationDecision
    validation_score: float
    validation_status: ValidationStatus
    confidence_scores: ConfidenceScores
    validation_checks: ValidationChecks
    decision_reason: str
    requires_manual_review: bool
    ocr_result: OCRResultResponse
    parsed_data: ParsedDataResponse
    processing_time_ms: float
    created_at: datetime


class ValidateTransferResponse(BaseModel):
    """Response endpoint validate transfer"""
    success: bool
    message: str
    data: Optional[ValidationResultResponse] = None
    error: Optional[str] = None


class ValidationListItem(BaseModel):
    """Item dalam list validasi"""
    id: int
    filename: str
    uploader_type: str
    uploader_id: str
    expected_nis: str
    expected_nama: str
    expected_amount: float
    extracted_amount: Optional[float]
    validation_status: str
    auto_decision: Optional[str]
    validation_score: Optional[float]
    created_at: datetime
    
    class Config:
        from_attributes = True


class ValidationListResponse(BaseModel):
    """Response list validasi"""
    success: bool
    total: int
    data: List[ValidationListItem]


class ValidationDetailResponse(BaseModel):
    """Response detail validasi"""
    success: bool
    data: Optional[Dict] = None
    error: Optional[str] = None


class ManualApprovalRequest(BaseModel):
    """Request manual approval/rejection"""
    validation_id: int
    status: ValidationStatus
    validated_by: str
    notes: Optional[str] = None


class ManualApprovalResponse(BaseModel):
    """Response manual approval"""
    success: bool
    message: str
    validation_id: int


class HealthCheckResponse(BaseModel):
    """Health check response"""
    status: str
    timestamp: datetime
    version: str
    ocr_ready: bool
    database_connected: bool


class ErrorResponse(BaseModel):
    """Error response"""
    success: bool = False
    error: str
    detail: Optional[str] = None
