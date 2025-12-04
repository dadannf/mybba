"""
=============================================
Unit Tests untuk OCR System
=============================================
"""

import pytest
import numpy as np
import cv2
from pathlib import Path

from app.utils.preprocessing import ImagePreprocessor
from app.services.ocr_service import OCRService
from app.utils.text_parser import BankTransferParser
from app.services.validation_service import ValidationService


@pytest.fixture
def preprocessor():
    return ImagePreprocessor()


@pytest.fixture
def ocr_service():
    return OCRService()


@pytest.fixture
def parser():
    return BankTransferParser()


@pytest.fixture
def validation_service():
    return ValidationService()


@pytest.fixture
def sample_image():
    """Create a simple test image"""
    img = np.ones((500, 800, 3), dtype=np.uint8) * 255
    cv2.putText(img, 'BCA', (50, 100), cv2.FONT_HERSHEY_SIMPLEX, 2, (0, 0, 0), 3)
    cv2.putText(img, 'Transfer: Rp 500.000', (50, 200), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 0), 2)
    cv2.putText(img, 'BAGAS PRATAMA', (50, 300), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 0), 2)
    return img


class TestPreprocessing:
    
    def test_resize_image(self, preprocessor, sample_image):
        resized = preprocessor.resize_image(sample_image, max_size=400)
        assert max(resized.shape[:2]) <= 400
    
    def test_denoise_image(self, preprocessor, sample_image):
        denoised = preprocessor.denoise_image(sample_image)
        assert denoised.shape == sample_image.shape
    
    def test_enhance_contrast(self, preprocessor, sample_image):
        enhanced = preprocessor.enhance_contrast(sample_image)
        assert enhanced.shape == sample_image.shape
    
    def test_correct_rotation(self, preprocessor, sample_image):
        rotated, angle = preprocessor.correct_rotation(sample_image)
        assert rotated.shape == sample_image.shape
        assert isinstance(angle, float)


class TestParser:
    
    def test_extract_bank_name(self, parser):
        text = "Transfer via BCA Mobile Banking"
        bank = parser.extract_bank_name(text)
        assert bank == "BCA"
    
    def test_extract_amount(self, parser):
        text = "Nominal: Rp 500.000"
        ocr_result = {'raw_text': text, 'results': []}
        amount = parser.extract_amount(text, [])
        assert amount == 500000.0
    
    def test_extract_account_number(self, parser):
        text = "No. Rekening: 1234567890"
        account = parser.extract_account_number(text, [])
        assert account == "1234567890"
    
    def test_match_expected_amount_exact(self, parser):
        score = parser.match_expected_amount(500000.0, 500000.0)
        assert score == 1.0
    
    def test_match_expected_amount_close(self, parser):
        score = parser.match_expected_amount(499500.0, 500000.0)
        assert score >= 0.9
    
    def test_match_expected_name(self, parser):
        score = parser.match_expected_name("BAGAS PRATAMA", "Bagas Pratama")
        assert score >= 0.9


class TestValidation:
    
    def test_check_ocr_quality_pass(self, validation_service):
        result = validation_service._check_ocr_quality(0.8, 0.7, 0.8)
        assert result is True
    
    def test_check_ocr_quality_fail(self, validation_service):
        result = validation_service._check_ocr_quality(0.5, 0.5, 0.5)
        assert result is False
    
    def test_check_amount_valid(self, validation_service):
        result = validation_service._check_amount(500000.0, 500000.0, 1.0)
        assert result is True
    
    def test_check_amount_invalid(self, validation_service):
        result = validation_service._check_amount(400000.0, 500000.0, 0.8)
        assert result is False
    
    def test_calculate_validation_score_high(self, validation_service):
        checks = {
            'ocr_quality': True,
            'amount_valid': True,
            'name_valid': True,
            'bank_detected': True,
            'date_detected': True,
        }
        score = validation_service._calculate_validation_score(checks, 1.0, 0.9, 0.85)
        assert score >= 85
    
    def test_make_decision_accept(self, validation_service):
        checks = {
            'ocr_quality': True,
            'amount_valid': True,
            'name_valid': True,
            'bank_detected': True,
            'date_detected': True,
        }
        decision, reason = validation_service._make_decision(checks, 92.0, 1.0)
        assert decision == 'accept'
    
    def test_make_decision_reject(self, validation_service):
        checks = {
            'ocr_quality': False,
            'amount_valid': False,
            'name_valid': False,
            'bank_detected': False,
            'date_detected': False,
        }
        decision, reason = validation_service._make_decision(checks, 30.0, 0.3)
        assert decision == 'reject'


class TestIntegration:
    
    def test_full_pipeline_mock(self, preprocessor, parser, validation_service):
        """Test full pipeline dengan mock OCR result"""
        
        # Mock OCR result
        ocr_result = {
            'raw_text': 'BCA\nTransfer: Rp 500.000\nBAGAS PRATAMA\n13 Nov 2025',
            'results': [
                {'bbox': [[0, 0], [100, 0], [100, 30], [0, 30]], 'text': 'BCA', 'confidence': 0.95},
                {'bbox': [[0, 50], [200, 50], [200, 80], [0, 80]], 'text': 'Transfer: Rp 500.000', 'confidence': 0.92},
                {'bbox': [[0, 100], [180, 100], [180, 130], [0, 130]], 'text': 'BAGAS PRATAMA', 'confidence': 0.88},
            ],
            'overall_confidence': 0.90,
            'detection_confidence': 0.92,
            'recognition_confidence': 0.88,
        }
        
        # Parse
        parsed_data = parser.parse(ocr_result)
        
        # Validate
        validation_result = validation_service.validate(
            ocr_result=ocr_result,
            parsed_data=parsed_data,
            expected_amount=500000.0,
            expected_nis="22211161",
            expected_nama="Bagas Pratama"
        )
        
        # Assertions
        assert validation_result['decision'] in ['accept', 'reject', 'review']
        assert 0 <= validation_result['validation_score'] <= 100
        assert 'confidence_scores' in validation_result
        assert 'validation_checks' in validation_result


if __name__ == "__main__":
    pytest.main([__file__, "-v"])
