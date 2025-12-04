"""
Simple OCR Test Script
Test OCR dengan berbagai preprocessing options
"""

from paddleocr import PaddleOCR
import cv2
import sys

def test_ocr(image_path):
    print(f"\n{'='*60}")
    print(f"Testing OCR on: {image_path}")
    print(f"{'='*60}\n")
    
    # Load image
    image = cv2.imread(image_path)
    if image is None:
        print("❌ Failed to load image!")
        return
    
    print(f"✅ Image loaded: {image.shape}")
    
    # Test 1: Original image with English model
    print("\n📝 Test 1: Original image + English model")
    try:
        ocr_en = PaddleOCR(use_textline_orientation=True, lang='en')
        results_en = ocr_en.ocr(image)
        if results_en and results_en[0]:
            print(f"✅ Detected {len(results_en[0])} text regions")
            for idx, line in enumerate(results_en[0][:5]):  # Show first 5
                text = line[1][0]
                conf = line[1][1]
                print(f"   {idx+1}. '{text}' (conf: {conf:.3f})")
        else:
            print("❌ No text detected")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    # Test 2: Original image with Latin model
    print("\n📝 Test 2: Original image + Latin model")
    try:
        ocr_latin = PaddleOCR(use_textline_orientation=True, lang='latin')
        results_latin = ocr_latin.ocr(image)
        if results_latin and results_latin[0]:
            print(f"✅ Detected {len(results_latin[0])} text regions")
            for idx, line in enumerate(results_latin[0][:5]):
                text = line[1][0]
                conf = line[1][1]
                print(f"   {idx+1}. '{text}' (conf: {conf:.3f})")
        else:
            print("❌ No text detected")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    # Test 3: Grayscale image
    print("\n📝 Test 3: Grayscale image + Latin model")
    try:
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
        rgb_gray = cv2.cvtColor(gray, cv2.COLOR_GRAY2RGB)
        results_gray = ocr_latin.ocr(rgb_gray)
        if results_gray and results_gray[0]:
            print(f"✅ Detected {len(results_gray[0])} text regions")
            for idx, line in enumerate(results_gray[0][:5]):
                text = line[1][0]
                conf = line[1][1]
                print(f"   {idx+1}. '{text}' (conf: {conf:.3f})")
        else:
            print("❌ No text detected")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    # Test 4: Contrast enhanced
    print("\n📝 Test 4: CLAHE enhanced + Latin model")
    try:
        lab = cv2.cvtColor(image, cv2.COLOR_BGR2LAB)
        l, a, b = cv2.split(lab)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8,8))
        l = clahe.apply(l)
        enhanced = cv2.merge([l, a, b])
        enhanced_rgb = cv2.cvtColor(enhanced, cv2.COLOR_LAB2RGB)
        
        results_enhanced = ocr_latin.ocr(enhanced_rgb)
        if results_enhanced and results_enhanced[0]:
            print(f"✅ Detected {len(results_enhanced[0])} text regions")
            for idx, line in enumerate(results_enhanced[0][:5]):
                text = line[1][0]
                conf = line[1][1]
                print(f"   {idx+1}. '{text}' (conf: {conf:.3f})")
        else:
            print("❌ No text detected")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    print(f"\n{'='*60}")
    print("Test complete!")
    print(f"{'='*60}\n")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python test_ocr_simple.py <image_path>")
        print("Example: python test_ocr_simple.py uploads/test_transfer.jpg")
        sys.exit(1)
    
    test_ocr(sys.argv[1])
