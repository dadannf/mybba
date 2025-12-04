#!/usr/bin/env python3
"""
Test OCR dengan gambar DANA
Gunakan gambar screenshot DANA yang sudah di-upload
"""

import requests
import json
import sys
from pathlib import Path

def test_ocr_dana(image_path):
    """Test OCR dengan gambar DANA"""
    
    # Check if file exists
    if not Path(image_path).exists():
        print(f"❌ File tidak ditemukan: {image_path}")
        return
    
    # OCR API endpoint
    url = "http://localhost:8000/api/v1/validate-transfer"
    
    # Expected data dari screenshot DANA
    expected_data = {
        'expected_amount': '200000',  # Total Bayar: Rp200.000
        'expected_nis': '22211161',   # Dummy NIS untuk test
        'expected_nama': 'AHMAD HILMI FAUZAN',  # Nama dari screenshot
        'uploader_type': 'admin',
        'uploader_id': 'admin'
    }
    
    print("=" * 80)
    print("🧪 TEST OCR SYSTEM - GAMBAR DANA")
    print("=" * 80)
    print(f"\n📁 File: {image_path}")
    print(f"💰 Expected Amount: Rp {expected_data['expected_amount']}")
    print(f"👤 Expected Name: {expected_data['expected_nama']}")
    print(f"\n🔄 Mengirim request ke OCR server...")
    print("-" * 80)
    
    try:
        # Open file and send request
        with open(image_path, 'rb') as f:
            files = {'file': f}
            response = requests.post(url, files=files, data=expected_data, timeout=60)
        
        print(f"\n📊 HTTP Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            
            if result.get('success'):
                data = result.get('data', {})
                
                print("\n✅ OCR BERHASIL!")
                print("=" * 80)
                
                # Parsed Data
                parsed = data.get('parsed_data', {})
                print("\n📋 DATA TERDETEKSI:")
                print(f"  🏦 Bank: {parsed.get('bank_name', 'N/A')}")
                print(f"  💳 Akun: {parsed.get('account_number', 'N/A')}")
                print(f"  👤 Nama: {parsed.get('account_name', 'N/A')}")
                
                # Handle None values for numeric formatting
                amount = parsed.get('transfer_amount')
                if amount is not None:
                    print(f"  💰 Nominal: Rp {float(amount):,.0f}")
                else:
                    print(f"  💰 Nominal: N/A")
                    
                print(f"  📅 Tanggal: {parsed.get('transfer_date', 'N/A')}")
                print(f"  🔖 Ref: {parsed.get('reference_number', 'N/A')}")
                
                # Confidence Scores
                confidence = data.get('confidence_scores', {})
                print("\n📊 CONFIDENCE SCORES:")
                
                # Safe numeric formatting
                overall_ocr = confidence.get('overall_ocr', 0)
                detection = confidence.get('detection', 0)
                recognition = confidence.get('recognition', 0)
                amount_match = confidence.get('amount_match', 0)
                
                print(f"  Overall OCR: {float(overall_ocr) * 100:.2f}%" if overall_ocr else "  Overall OCR: 0.00%")
                print(f"  Detection: {float(detection) * 100:.2f}%" if detection else "  Detection: 0.00%")
                print(f"  Recognition: {float(recognition) * 100:.2f}%" if recognition else "  Recognition: 0.00%")
                print(f"  Amount Match: {float(amount_match) * 100:.2f}%" if amount_match else "  Amount Match: 0.00%")
                
                # Decision
                decision = data.get('decision', 'review')
                score = data.get('validation_score', 0)
                
                print("\n🎯 DECISION:")
                if decision == 'accept':
                    print(f"  ✅ AUTO APPROVED ({score:.1f}%)")
                elif decision == 'reject':
                    print(f"  ❌ AUTO REJECTED ({score:.1f}%)")
                else:
                    print(f"  ⚠️  NEED REVIEW ({score:.1f}%)")
                
                print(f"\n💬 Reason: {data.get('decision_reason', 'N/A')}")
                
                # Raw Text (first 500 chars)
                raw_text = data.get('raw_text', '')
                if raw_text:
                    print("\n📝 RAW TEXT DETECTED:")
                    print("-" * 80)
                    print(raw_text[:500])
                    if len(raw_text) > 500:
                        print(f"... ({len(raw_text) - 500} characters more)")
                
                print("\n" + "=" * 80)
                print("✅ TEST SELESAI - OCR BERHASIL MENDETEKSI")
                print("=" * 80)
                
            else:
                print(f"\n❌ OCR GAGAL: {result.get('message', 'Unknown error')}")
                
        else:
            print(f"\n❌ SERVER ERROR")
            try:
                error_data = response.json()
                print(f"Detail: {json.dumps(error_data, indent=2)}")
            except:
                print(f"Response: {response.text[:500]}")
    
    except requests.exceptions.ConnectionError:
        print("\n❌ TIDAK DAPAT TERHUBUNG KE SERVER")
        print("Pastikan OCR server berjalan di http://localhost:8000")
        print("\nJalankan server dengan:")
        print("  cd F:\\laragon\\www\\mybba\\ocr_system")
        print("  python -m uvicorn main:app --reload")
        
    except requests.exceptions.Timeout:
        print("\n❌ REQUEST TIMEOUT (> 60 detik)")
        print("Server mungkin sedang sibuk atau gambar terlalu besar")
        
    except Exception as e:
        print(f"\n❌ ERROR: {str(e)}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    # Default path - sesuaikan dengan lokasi gambar Anda
    default_image = "uploads/test_ocr/dana_screenshot.jpg"
    
    if len(sys.argv) > 1:
        image_path = sys.argv[1]
    else:
        image_path = default_image
        print(f"ℹ️  Menggunakan default image: {image_path}")
        print(f"ℹ️  Atau gunakan: python test_dana_ocr.py <path_to_image>\n")
    
    test_ocr_dana(image_path)
