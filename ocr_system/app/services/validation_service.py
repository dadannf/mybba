"""
=============================================
Validation Service Module
Decision Logic untuk Accept/Reject Bukti Transfer
=============================================
"""

from typing import Dict, Tuple, Optional
from loguru import logger
from app.core.config import settings
from app.utils.text_parser import BankTransferParser


class ValidationService:
    """
    Service untuk validasi bukti transfer
    Decision logic: ACCEPT, REJECT, atau REVIEW (manual check)
    """
    
    def __init__(self):
        self.parser = BankTransferParser()
        
        # Thresholds
        self.min_overall_confidence = settings.min_confidence_score
        self.min_detection_confidence = settings.min_text_detection_score
        self.min_recognition_confidence = settings.min_recognition_score
        self.min_amount_match = settings.min_amount_match_threshold
    
    def validate(self, 
                 ocr_result: Dict,
                 parsed_data: Dict,
                 expected_amount: float,
                 expected_nis: str,
                 expected_nama: str) -> Dict:
        """
        Main validation function
        
        Args:
            ocr_result: hasil dari OCR service
            parsed_data: hasil dari parser
            expected_amount: nominal yang diharapkan
            expected_nis: NIS siswa
            expected_nama: nama siswa
        
        Returns:
            dict dengan validation result dan decision
        """
        logger.info(f"Validating transfer for NIS: {expected_nis}, "
                   f"Expected amount: Rp {expected_amount:,.0f}")
        
        # Extract data
        extracted_amount = parsed_data.get('transfer_amount')
        extracted_name = parsed_data.get('account_name')
        overall_confidence = ocr_result.get('overall_confidence', 0.0)
        detection_conf = ocr_result.get('detection_confidence', 0.0)
        recognition_conf = ocr_result.get('recognition_confidence', 0.0)
        
        # Calculate match scores
        amount_match_score = self.parser.match_expected_amount(
            extracted_amount, expected_amount, tolerance=0.01
        )
        
        name_match_score = self.parser.match_expected_name(
            extracted_name, expected_nama, threshold=0.7
        )
        
        # Validation checks
        validation_checks = {
            'ocr_quality': self._check_ocr_quality(overall_confidence, detection_conf, recognition_conf),
            'amount_valid': self._check_amount(extracted_amount, expected_amount, amount_match_score),
            'name_valid': self._check_name(extracted_name, expected_nama, name_match_score),
            'bank_detected': parsed_data.get('bank_name') is not None,
            'date_detected': parsed_data.get('transfer_date') is not None,
        }
        
        # Calculate overall validation score
        validation_score = self._calculate_validation_score(
            validation_checks, amount_match_score, name_match_score, overall_confidence
        )
        
        # Make decision
        decision, decision_reason = self._make_decision(
            validation_checks, validation_score, amount_match_score
        )
        
        # Prepare result
        result = {
            'decision': decision,  # 'accept', 'reject', 'review'
            'validation_score': validation_score,
            'validation_checks': validation_checks,
            'confidence_scores': {
                'overall_ocr': overall_confidence,
                'detection': detection_conf,
                'recognition': recognition_conf,
                'amount_match': amount_match_score,
                'name_match': name_match_score,
            },
            'extracted_data': {
                'amount': extracted_amount,
                'name': extracted_name,
                'bank': parsed_data.get('bank_name'),
                'date': parsed_data.get('transfer_date'),
                'reference': parsed_data.get('reference_number'),
            },
            'expected_data': {
                'amount': expected_amount,
                'name': expected_nama,
                'nis': expected_nis,
            },
            'decision_reason': decision_reason,
            'requires_manual_review': decision == 'review',
        }
        
        logger.info(f"Validation complete. Decision: {decision}, Score: {validation_score:.2f}")
        
        return result
    
    def _check_ocr_quality(self, overall: float, detection: float, recognition: float) -> bool:
        """Check if OCR quality meets thresholds"""
        return (overall >= self.min_overall_confidence and
                detection >= self.min_detection_confidence and
                recognition >= self.min_recognition_confidence)
    
    def _check_amount(self, extracted: Optional[float], expected: float, match_score: float) -> bool:
        """Check if extracted amount matches expected"""
        if extracted is None:
            return False
        return match_score >= self.min_amount_match
    
    def _check_name(self, extracted: Optional[str], expected: str, match_score: float) -> bool:
        """Check if name matches (with fuzzy matching)"""
        if not extracted:
            return False
        # Name check is more lenient (70% match)
        return match_score >= 0.70
    
    def _calculate_validation_score(self, checks: Dict, 
                                    amount_match: float,
                                    name_match: float,
                                    ocr_conf: float) -> float:
        """
        Calculate overall validation score (0-100)
        Weighted combination of all factors
        """
        weights = {
            'amount': 0.40,      # Amount match most important (40%)
            'ocr_quality': 0.25,  # OCR quality (25%)
            'name': 0.20,        # Name match (20%)
            'bank': 0.10,        # Bank detected (10%)
            'date': 0.05,        # Date detected (5%)
        }
        
        score = 0.0
        score += amount_match * weights['amount'] * 100
        score += (1.0 if checks['ocr_quality'] else 0.0) * weights['ocr_quality'] * 100
        score += name_match * weights['name'] * 100
        score += (1.0 if checks['bank_detected'] else 0.0) * weights['bank'] * 100
        score += (1.0 if checks['date_detected'] else 0.0) * weights['date'] * 100
        
        return min(100.0, score)
    
    def _make_decision(self, checks: Dict, score: float, amount_match: float) -> Tuple[str, str]:
        """
        Make final decision: accept, reject, or review
        
        Returns:
            (decision, reason)
        """
        reasons = []
        
        # Auto ACCEPT conditions
        if (score >= 85 and 
            checks['amount_valid'] and 
            checks['ocr_quality'] and
            amount_match >= 0.98):
            return 'accept', 'Semua validasi terpenuhi dengan confidence tinggi'
        
        # Auto REJECT conditions
        if not checks['ocr_quality']:
            reasons.append('Kualitas OCR rendah (gambar tidak jelas)')
        
        if not checks['amount_valid']:
            reasons.append('Nominal tidak sesuai dengan yang diharapkan')
        
        if score < 50:
            reasons.append('Validation score terlalu rendah')
        
        if len(reasons) >= 2:  # Multiple critical failures
            return 'reject', '; '.join(reasons)
        
        # REVIEW conditions (borderline cases)
        if 50 <= score < 85:
            reasons.append('Perlu review manual - confidence score borderline')
        
        if checks['amount_valid'] and amount_match < 0.98:
            reasons.append('Nominal mendekati tapi tidak exact match')
        
        if not checks['name_valid']:
            reasons.append('Nama penerima tidak terdeteksi atau tidak match')
        
        if not checks['bank_detected']:
            reasons.append('Nama bank tidak terdeteksi')
        
        if reasons:
            return 'review', '; '.join(reasons)
        
        # Default: review if not clearly accept
        return 'review', 'Default - perlu review manual'
    
    def validate_batch(self, validations: list) -> Dict:
        """
        Validate multiple transfers
        Returns summary statistics
        """
        results = {
            'total': len(validations),
            'accepted': 0,
            'rejected': 0,
            'review': 0,
            'avg_score': 0.0,
        }
        
        total_score = 0.0
        for validation in validations:
            decision = validation.get('decision')
            if decision == 'accept':
                results['accepted'] += 1
            elif decision == 'reject':
                results['rejected'] += 1
            else:
                results['review'] += 1
            
            total_score += validation.get('validation_score', 0.0)
        
        results['avg_score'] = total_score / len(validations) if validations else 0.0
        
        return results


# Singleton
_validation_service_instance = None


def get_validation_service() -> ValidationService:
    """Get singleton validation service"""
    global _validation_service_instance
    if _validation_service_instance is None:
        _validation_service_instance = ValidationService()
    return _validation_service_instance
