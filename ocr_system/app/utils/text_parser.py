"""
=============================================
Text Parsing & Information Extraction Module
Parse informasi bank dari hasil OCR
=============================================
"""

import re
from typing import Dict, Optional, List
from datetime import datetime
from loguru import logger
import difflib


class BankTransferParser:
    """
    Parser untuk mengekstrak informasi dari bukti transfer bank Indonesia
    Mendukung berbagai format bank: BCA, Mandiri, BNI, BRI, dll
    """
    
    # Bank names patterns (Hanya BRI yang diterima)
    BANK_PATTERNS = {
        'BRI': r'(?i)(bri|bank\s+rakyat\s+indonesia)',
        # E-Wallets (untuk backward compatibility OCR detection)
        'DANA': r'(?i)\bDANA\b',
        'GOPAY': r'(?i)(gopay|go-pay)',
        'OVO': r'(?i)\bOVO\b',
        'SHOPEEPAY': r'(?i)(shopee\s?pay|spay)',
        'LINKAJA': r'(?i)(link\s?aja|linkaja)',
    }
    
    # Amount patterns (Rupiah) - Enhanced for E-Wallets
    AMOUNT_PATTERNS = [
        # E-Wallet specific patterns (PRIORITY)
        r'(?i)(?:total\s+bayar|total\s+pembayaran)[\s:]*(?:rp\.?)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*)',
        r'(?i)kirim\s+uang\s+(?:rp\.?)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*)',
        r'(?i)terima\s+(?:rp\.?)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*)',
        # Traditional bank patterns
        r'(?i)(?:rp\.?|idr\.?|rupiah)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?)',
        r'(?i)(?:nominal|jumlah|total|amount)[\s:]*(?:rp\.?|idr\.?)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?)',
        r'([0-9]{1,3}(?:[.,][0-9]{3})+(?:[.,][0-9]{2})?)',  # Format: 1.000.000,00 atau 1,000,000.00
    ]
    
    # Account number patterns
    ACCOUNT_PATTERNS = [
        r'(?i)(?:no\.?\s*rek(?:ening)?|account\s*no\.?|acc\.?\s*no\.?)[\s:]*([0-9\s-]{8,20})',
        r'(?i)(?:ke\s*rekening|to\s*account)[\s:]*([0-9\s-]{8,20})',
        r'\b([0-9]{10,16})\b',  # Generic 10-16 digit number
    ]
    
    # Date patterns
    DATE_PATTERNS = [
        r'(\d{1,2}[-/]\d{1,2}[-/]\d{2,4})',  # DD-MM-YYYY or DD/MM/YYYY
        r'(\d{4}[-/]\d{1,2}[-/]\d{1,2})',    # YYYY-MM-DD
        r'(\d{1,2}\s+(?:Jan(?:uari)?|Feb(?:ruari)?|Mar(?:et)?|Apr(?:il)?|Mei|Jun(?:i)?|Jul(?:i)?|Agu(?:stus)?|Sep(?:tember)?|Okt(?:ober)?|Nov(?:ember)?|Des(?:ember)?)\s+\d{2,4})',
    ]
    
    # Reference number patterns
    REFERENCE_PATTERNS = [
        r'(?i)(?:ref(?:erence)?|no\.?\s*ref|trx\s*id|transaction\s*id)[\s:]*([A-Z0-9]{6,20})',
        r'\b([A-Z]{2,3}[0-9]{10,16})\b',  # Format: ABC1234567890
    ]
    
    def __init__(self):
        self.extracted_data = {}
    
    def parse(self, ocr_result: Dict) -> Dict:
        """
        Main parsing function
        
        Args:
            ocr_result: hasil dari OCR service
        
        Returns:
            dict dengan parsed information
        """
        raw_text = ocr_result.get('raw_text', '')
        results = ocr_result.get('results', [])
        
        logger.info("Starting text parsing...")
        
        parsed = {
            'bank_name': self.extract_bank_name(raw_text),
            'account_number': self.extract_account_number(raw_text, results),
            'account_name': self.extract_account_name(raw_text, results),
            'transfer_amount': self.extract_amount(raw_text, results),
            'transfer_date': self.extract_date(raw_text),
            'reference_number': self.extract_reference(raw_text),
            'confidence_scores': self.calculate_field_confidence(raw_text, results),
            'all_numbers': self.extract_all_numbers(raw_text),
            'all_names': self.extract_potential_names(results),
        }
        
        logger.info(f"Parsing complete. Bank: {parsed['bank_name']}, "
                   f"Amount: {parsed['transfer_amount']}")
        
        return parsed
    
    def extract_bank_name(self, text: str) -> Optional[str]:
        """Extract bank name"""
        for bank, pattern in self.BANK_PATTERNS.items():
            if re.search(pattern, text):
                return bank
        return None
    
    def extract_amount(self, text: str, results: List[Dict]) -> Optional[float]:
        """
        Extract transfer amount
        Mencoba berbagai pattern dan pilih yang paling mungkin
        Supports Indonesian format (titik = thousand separator, koma = decimal)
        """
        candidates = []
        
        for pattern in self.AMOUNT_PATTERNS:
            matches = re.finditer(pattern, text, re.MULTILINE)
            for match in matches:
                amount_str = match.group(1) if match.lastindex else match.group(0)
                # Clean and convert
                amount_str = re.sub(r'[^\d,.]', '', amount_str)
                
                # Handle different decimal separators
                # Indonesian format: 200.000 = 200 ribu, 200.000,50 = 200 ribu 50 sen
                # US format: 200,000 = 200 ribu, 200,000.50 = 200 ribu 50 sen
                
                if ',' in amount_str and '.' in amount_str:
                    # Determine which is decimal separator based on position
                    last_comma_idx = amount_str.rindex(',')
                    last_dot_idx = amount_str.rindex('.')
                    
                    if last_comma_idx > last_dot_idx:
                        # Comma is decimal separator (Indonesian/European: 1.000.000,50)
                        amount_str = amount_str.replace('.', '').replace(',', '.')
                    else:
                        # Dot is decimal separator (US: 1,000,000.50)
                        amount_str = amount_str.replace(',', '')
                        
                elif ',' in amount_str:
                    # Check if it's thousands separator or decimal
                    parts = amount_str.split(',')
                    if len(parts[-1]) == 2:  # Decimal separator (1.000,50)
                        amount_str = amount_str.replace(',', '.')
                    else:  # Thousands separator (1,000)
                        amount_str = amount_str.replace(',', '')
                        
                elif '.' in amount_str:
                    # Check if it's thousands separator or decimal
                    parts = amount_str.split('.')
                    if len(parts[-1]) == 2 and len(parts) == 2:  # Decimal (200.50)
                        # Keep as is
                        pass
                    elif len(parts[-1]) == 3:  # Thousands separator (200.000)
                        # Indonesian format - remove dots
                        amount_str = amount_str.replace('.', '')
                    else:
                        # Keep as is
                        pass
                
                try:
                    amount = float(amount_str)
                    # Filter reasonable amounts (10k - 50M) - lowered minimum for flexibility
                    if 10000 <= amount <= 50000000:
                        candidates.append(amount)
                        logger.debug(f"Amount candidate: {amount:,.0f} from '{match.group(0)}'")
                except ValueError:
                    continue
        
        # Return most common amount or highest
        if candidates:
            most_common = max(set(candidates), key=candidates.count)
            logger.info(f"Extracted amount: Rp {most_common:,.0f} from {len(candidates)} candidates")
            return most_common
        return None
    
    def extract_account_number(self, text: str, results: List[Dict]) -> Optional[str]:
        """Extract account number"""
        for pattern in self.ACCOUNT_PATTERNS:
            match = re.search(pattern, text)
            if match:
                acc_num = match.group(1).replace(' ', '').replace('-', '')
                if 8 <= len(acc_num) <= 20:
                    return acc_num
        return None
    
    def extract_account_name(self, text: str, results: List[Dict]) -> Optional[str]:
        """
        Extract account holder name
        Biasanya ada setelah kata-kata: "Nama", "Penerima", "Beneficiary", "To"
        Enhanced untuk support e-wallet format
        """
        patterns = [
            # E-Wallet specific (DANA: "Kirim Uang Rp200.000 ke AHMAD HILMI FAUZAN -")
            r'(?i)kirim\s+uang\s+(?:rp\.?)?\s*[0-9.,]+\s+ke\s+([A-Z][A-Z\s]{5,50})(?:\s*-)?',
            r'(?i)terima\s+dari\s+([A-Z][A-Z\s]{5,50})',
            r'(?i)(?:nama|penerima|beneficiary|to|account\s+name)[\s:]+([A-Z][A-Z\s.]{5,50})',
            r'(?i)(?:atas\s+nama)[\s:]+([A-Z][A-Z\s.]{5,50})',
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text)
            if match:
                name = match.group(1).strip()
                # Clean up
                name = re.sub(r'\s+', ' ', name)
                if 3 <= len(name) <= 50:
                    return name
        
        # Fallback: extract from OCR results (high confidence capital text)
        for result in results:
            if result['confidence'] > 0.85:
                text_segment = result['text']
                # Check if it's mostly uppercase and reasonable length
                if text_segment.isupper() and 5 <= len(text_segment) <= 50:
                    # Check if it's not a bank name or other keyword
                    if not any(bank in text_segment for bank in self.BANK_PATTERNS.keys()):
                        return text_segment
        
        return None
    
    def extract_date(self, text: str) -> Optional[datetime]:
        """Extract transfer date"""
        for pattern in self.DATE_PATTERNS:
            match = re.search(pattern, text)
            if match:
                date_str = match.group(1)
                # Try to parse
                date_formats = [
                    '%d-%m-%Y', '%d/%m/%Y', '%Y-%m-%d', '%Y/%m/%d',
                    '%d-%m-%y', '%d/%m/%y',
                    '%d %B %Y', '%d %b %Y',
                ]
                
                for fmt in date_formats:
                    try:
                        return datetime.strptime(date_str, fmt)
                    except ValueError:
                        continue
        return None
    
    def extract_reference(self, text: str) -> Optional[str]:
        """Extract reference/transaction number"""
        for pattern in self.REFERENCE_PATTERNS:
            match = re.search(pattern, text)
            if match:
                return match.group(1)
        return None
    
    def extract_all_numbers(self, text: str) -> List[str]:
        """Extract all number sequences (for debugging)"""
        return re.findall(r'\b\d+(?:[.,]\d+)*\b', text)
    
    def extract_potential_names(self, results: List[Dict]) -> List[str]:
        """Extract all potential name strings"""
        names = []
        for result in results:
            text = result['text']
            # Uppercase text with reasonable length
            if text.isupper() and 5 <= len(text) <= 50:
                if result['confidence'] > 0.7:
                    names.append(text)
        return names
    
    def calculate_field_confidence(self, text: str, results: List[Dict]) -> Dict[str, float]:
        """Calculate confidence for each extracted field"""
        return {
            'bank_name': 1.0 if self.extract_bank_name(text) else 0.0,
            'account_number': 0.8 if self.extract_account_number(text, results) else 0.0,
            'account_name': 0.7 if self.extract_account_name(text, results) else 0.0,
            'transfer_amount': 0.9 if self.extract_amount(text, results) else 0.0,
            'transfer_date': 0.8 if self.extract_date(text) else 0.0,
            'reference_number': 0.6 if self.extract_reference(text) else 0.0,
        }
    
    def match_expected_amount(self, extracted_amount: Optional[float], 
                             expected_amount: float,
                             tolerance: float = 0.01) -> float:
        """
        Compare extracted amount with expected amount
        Returns match score (0.0 - 1.0)
        """
        if extracted_amount is None:
            return 0.0
        
        diff = abs(extracted_amount - expected_amount)
        tolerance_amount = expected_amount * tolerance
        
        if diff == 0:
            return 1.0
        elif diff <= tolerance_amount:
            return 1.0 - (diff / tolerance_amount) * 0.1  # 0.9 - 1.0
        elif diff <= expected_amount * 0.05:  # Within 5%
            return 0.8
        elif diff <= expected_amount * 0.10:  # Within 10%
            return 0.6
        else:
            return 0.3
    
    def match_expected_name(self, extracted_name: Optional[str],
                           expected_name: str,
                           threshold: float = 0.7) -> float:
        """
        Fuzzy match name using Levenshtein distance
        Returns similarity score (0.0 - 1.0)
        """
        if not extracted_name or not expected_name:
            return 0.0
        
        # Normalize
        ext_name = extracted_name.upper().strip()
        exp_name = expected_name.upper().strip()
        
        # Use difflib for fuzzy matching
        similarity = difflib.SequenceMatcher(None, ext_name, exp_name).ratio()
        
        return similarity


# Singleton
_parser_instance = None


def get_parser() -> BankTransferParser:
    """Get singleton parser instance"""
    global _parser_instance
    if _parser_instance is None:
        _parser_instance = BankTransferParser()
    return _parser_instance
