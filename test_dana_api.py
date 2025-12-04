"""
Test DANA OCR melalui Parser langsung
"""
import sys
import os

# Change to ocr_system directory
ocr_dir = os.path.join(os.path.dirname(__file__), 'ocr_system')
os.chdir(ocr_dir)
sys.path.insert(0, ocr_dir)

from app.services.ocr_service import OCRService
from app.utils.text_parser import BankTransferParser
from app.services.validation_service import ValidationService
import cv2

def test_full_pipeline():
    print("\n" + "="*80)
    print("TEST FULL OCR PIPELINE - DANA")
    print("="*80)
    
    image_path = r"F:\laragon\www\mybba\public\uploads\bukti_bayar\dana_transfer.jpg"
    
    if not os.path.exists(image_path):
        print(f"\n❌ File not found: {image_path}")
        return
    
    # Initialize services
    print("\n⏳ Loading services...")
    ocr_service = OCRService()
    parser = BankTransferParser()
    validator = ValidationService()
    
    # Read image
    print(f"📖 Reading image...")
    image = cv2.imread(image_path)
    height, width = image.shape[:2]
    print(f"📐 Size: {width}x{height}px")
    
    # Step 1: OCR
    print("\n🔍 Step 1: OCR Detection & Recognition...")
    print("-" * 80)
    ocr_result = ocr_service.detect_and_recognize(image)
    
    print(f"✅ OCR Complete")
    print(f"   Overall Confidence: {ocr_result.get('overall_confidence', 0)*100:.2f}%")
    print(f"   Text Regions: {ocr_result.get('num_regions', 0)}")
    
    # Step 2: Parse
    print("\n📋 Step 2: Parsing Transfer Information...")
    print("-" * 80)
    parsed_data = parser.parse(ocr_result)
    
    print(f"✅ Parsing Complete")
    print(f"   Bank: {parsed_data.get('bank_name', 'N/A')}")
    print(f"   Amount: {parsed_data.get('transfer_amount', 'N/A')}")
    if parsed_data.get('transfer_amount'):
        print(f"          → Rp {parsed_data['transfer_amount']:,.0f}")
    print(f"   Name: {parsed_data.get('account_name', 'N/A')}")
    print(f"   Date: {parsed_data.get('transfer_date', 'N/A')}")
    
    # Step 3: Validate
    print("\n✔️  Step 3: Validation...")
    print("-" * 80)
    
    expected_amount = 200000
    expected_nis = "22211161"
    expected_nama = "AHMAD HILMI FAUZAN"
    
    validation_result = validator.validate(
        ocr_result=ocr_result,
        parsed_data=parsed_data,
        expected_amount=expected_amount,
        expected_nis=expected_nis,
        expected_nama=expected_nama
    )
    
    print(f"✅ Validation Complete")
    print(f"\n📊 Validation Results:")
    print(f"   Decision: {validation_result['decision'].upper()}")
    print(f"   Validation Score: {validation_result['validation_score']:.2f}%")
    print(f"   Reason: {validation_result['decision_reason']}")
    
    print(f"\n🎯 Validation Checks:")
    checks = validation_result['validation_checks']
    for check, passed in checks.items():
        status = "✅" if passed else "❌"
        print(f"   {status} {check}: {passed}")
    
    print(f"\n📈 Confidence Scores:")
    scores = validation_result['confidence_scores']
    for score_name, score_val in scores.items():
        print(f"   • {score_name}: {score_val*100:.2f}%")
    
    print(f"\n📌 Expected vs Extracted:")
    print(f"   Expected Amount: Rp {expected_amount:,}")
    print(f"   Extracted Amount: Rp {validation_result['extracted_data']['amount']:,.0f}" if validation_result['extracted_data']['amount'] else "   Extracted Amount: None")
    print(f"   Expected Name: {expected_nama}")
    print(f"   Extracted Name: {validation_result['extracted_data']['name']}")
    
    # Final verdict
    print("\n" + "="*80)
    decision = validation_result['decision']
    if decision == 'accept':
        print("HASIL: AUTO-APPROVED")
        print("   Pembayaran akan otomatis diterima!")
    elif decision == 'reject':
        print("HASIL: AUTO-REJECTED")
        print("   Pembayaran ditolak otomatis!")
    else:
        print("HASIL: NEED REVIEW")
        print("   Perlu verifikasi manual admin!")
    print("="*80 + "\n")

if __name__ == "__main__":
    try:
        test_full_pipeline()
    except Exception as e:
        print(f"\nERROR: {e}")
        import traceback
        traceback.print_exc()
