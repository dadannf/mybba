"""
Test OCR dengan bukti transfer DANA
Nominal: Rp200.000
Penerima: AHMAD HILMI FAUZAN - BCA
"""
import sys
import os

# Change to ocr_system directory and add to path
ocr_dir = os.path.join(os.path.dirname(__file__), 'ocr_system')
os.chdir(ocr_dir)
sys.path.insert(0, ocr_dir)

from app.services.ocr_service import OCRService
from app.core.config import Settings
import cv2

def test_dana_transfer():
    print("\n" + "="*80)
    print("🧪 TEST OCR - BUKTI TRANSFER DANA")
    print("="*80)
    
    # Path ke gambar (sesuaikan dengan lokasi gambar Anda)
    image_path = input("\n📂 Masukkan path lengkap gambar DANA (atau tekan Enter untuk default): ").strip()
    
    if not image_path:
        # Default path - sesuaikan dengan lokasi gambar Anda
        image_path = r"F:\laragon\www\mybba\public\uploads\bukti_bayar\dana_transfer.jpg"
    
    # Convert to absolute path from ocr_system directory
    if not os.path.isabs(image_path):
        image_path = os.path.abspath(os.path.join('..', image_path))
    
    if not os.path.exists(image_path):
        print(f"\n❌ File tidak ditemukan: {image_path}")
        print("\n💡 Silakan copy gambar DANA ke folder:")
        print("   F:\\laragon\\www\\mybba\\public\\uploads\\bukti_bayar\\")
        print("   Nama file: dana_transfer.jpg")
        return
    
    # Load settings
    settings = Settings()
    
    # Initialize OCR service
    print("\n⏳ Loading PaddleOCR model...")
    ocr_service = OCRService()  # Uses global settings
    
    # Read image
    print(f"📖 Reading image: {os.path.basename(image_path)}")
    image = cv2.imread(image_path)
    
    if image is None:
        print(f"❌ Gagal membaca gambar!")
        return
    
    height, width = image.shape[:2]
    print(f"📐 Image size: {width}x{height}px")
    
    # Process OCR
    print("\n🔍 Processing OCR...")
    print("-" * 80)
    
    result = ocr_service.detect_and_recognize(image)
    
    # Display results
    print("\n" + "="*80)
    print("📊 HASIL OCR")
    print("="*80)
    
    # Check if OCR detected any text
    if result.get('num_regions', 0) > 0:
        print(f"\n✅ Status: SUCCESS")
        print(f"📈 Overall Confidence: {result.get('overall_confidence', 0)*100:.2f}%")
        print(f"🎯 Detection Confidence: {result.get('detection_confidence', 0)*100:.2f}%")
        print(f"🎯 Recognition Confidence: {result.get('recognition_confidence', 0)*100:.2f}%")
        print(f"⏱️  Processing Time: {result.get('processing_time_ms', 0):.0f}ms")
        
        # Raw text detected
        if result.get('results'):
            print(f"\n📝 TEKS YANG TERDETEKSI ({len(result['results'])} lines):")
            for i, line in enumerate(result['results'][:20], 1):
                text = line.get('text', '')
                conf = line.get('confidence', 0)
                print(f"   {i:2d}. [{conf*100:5.1f}%] {text}")
            if len(result['results']) > 20:
                print(f"   ... dan {len(result['results']) - 20} baris lainnya")
        
        # Now parse transfer info manually from text
        print(f"\n📋 DATA YANG TERDETEKSI:")
        raw_text = result.get('raw_text', '')
        
        # Simple parsing untuk demo
        import re
        
        # Detect DANA
        if 'DANA' in raw_text.upper():
            print(f"   🏦 Bank/E-Wallet: DANA")
        
        # Detect BCA
        if 'BCA' in raw_text.upper():
            print(f"   🏦 Rekening Tujuan: BCA")
        
        # Detect nominal (Rp200.000 atau Rp 200.000)
        nominal_pattern = r'Rp\s?(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)'
        nominal_matches = re.findall(nominal_pattern, raw_text)
        if nominal_matches:
            print(f"   💰 Nominal terdeteksi:")
            for nom in set(nominal_matches):
                print(f"       → Rp {nom}")
        
        # Detect nama
        if 'AHMAD' in raw_text.upper() or 'HILMI' in raw_text.upper() or 'FAUZAN' in raw_text.upper():
            nama_lines = [line for line in result['results'] if 'AHMAD' in line['text'].upper() or 'HILMI' in line['text'].upper() or 'FAUZAN' in line['text'].upper()]
            if nama_lines:
                print(f"   👤 Nama: {nama_lines[0]['text']}")
        
        # Detect tanggal
        tanggal_pattern = r'\d{1,2}\s(?:Des|Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Sep|Okt|Nov|Des)\s\d{4}'
        tanggal_matches = re.findall(tanggal_pattern, raw_text)
        if tanggal_matches:
            print(f"   📅 Tanggal: {tanggal_matches[0]}")
        
        # Expected data untuk validasi
        print("\n" + "="*80)
        print("🎯 VALIDASI DENGAN DATA EXPECTED")
        print("="*80)
        
        expected_amount = 200000
        expected_name = "AHMAD HILMI FAUZAN"
        
        print(f"\n📌 Expected:")
        print(f"   💰 Nominal: Rp {expected_amount:,}")
        print(f"   👤 Nama: {expected_name}")
        
        print(f"\n✔️  Match Check:")
        
        # Check nominal 200.000
        if '200.000' in raw_text or '200000' in raw_text:
            print(f"   ✅ Nominal Rp200.000 TERDETEKSI")
        else:
            print(f"   ❌ Nominal Rp200.000 TIDAK TERDETEKSI")
        
        # Check nama
        name_found = False
        for word in expected_name.split():
            if word.upper() in raw_text.upper():
                name_found = True
                break
        
        if name_found:
            print(f"   ✅ Nama TERDETEKSI")
        else:
            print(f"   ❌ Nama TIDAK TERDETEKSI")
        
        # Check BCA
        if 'BCA' in raw_text.upper():
            print(f"   ✅ Bank BCA TERDETEKSI")
        else:
            print(f"   ❌ Bank BCA TIDAK TERDETEKSI")
        
    else:
        print(f"\n❌ Status: FAILED - Tidak ada teks terdeteksi")
        if 'error' in result:
            print(f"⚠️  Error: {result.get('error')}")
    
    print("\n" + "="*80)

if __name__ == "__main__":
    try:
        test_dana_transfer()
    except KeyboardInterrupt:
        print("\n\n⚠️  Test dibatalkan oleh user")
    except Exception as e:
        print(f"\n\n❌ ERROR: {str(e)}")
        import traceback
        traceback.print_exc()
