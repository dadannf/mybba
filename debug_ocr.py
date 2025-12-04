#!/usr/bin/env python3
"""
Direct OCR Test - Bypass server untuk debugging
"""

import cv2
import sys
from paddleocr import PaddleOCR

def test_direct_ocr(image_path):
    print("=" * 80)
    print("🔍 DIRECT OCR TEST - DEBUGGING MODE")
    print("=" * 80)
    
    # Load image
    print(f"\n1. Loading image: {image_path}")
    img = cv2.imread(image_path)
    if img is None:
        print("❌ Failed to load image!")
        return
    
    h, w = img.shape[:2]
    print(f"   ✅ Image loaded: {w}x{h}px")
    
    # Initialize PaddleOCR
    print("\n2. Initializing PaddleOCR...")
    try:
        ocr = PaddleOCR(use_textline_orientation=True, lang='en')
        print("   ✅ PaddleOCR initialized (lang='en')")
    except Exception as e:
        print(f"   ❌ Failed to initialize: {e}")
        return
    
    # Run OCR
    print("\n3. Running OCR detection...")
    try:
        # Use predict() instead of ocr() for newer PaddleOCR versions
        results = ocr.predict(img)
        print(f"   ✅ OCR completed")
        print(f"   Results type: {type(results)}")
        
        # Check if results is a dict (new format)
        if isinstance(results, dict):
            print(f"   Results is dict with keys: {results.keys()}")
        else:
            print(f"   Results length: {len(results) if results else 0}")
    except Exception as e:
        print(f"   ❌ OCR failed: {e}")
        import traceback
        traceback.print_exc()
        return
    
    # Analyze results
    print("\n4. Analyzing results...")
    print("-" * 80)
    
    # Handle dict format (new PaddleOCR API)
    if isinstance(results, dict):
        print("   📦 Results is a dictionary")
        print(f"   Keys: {list(results.keys())}")
        
        # Extract text results
        if 'rec_texts' in results:
            texts = results['rec_texts']
            scores = results.get('rec_scores', [])
            polys = results.get('dt_polys', [])
            
            print(f"\n   Total detected texts: {len(texts)}")
            print("\n" + "=" * 80)
            print("DETAILED RESULTS:")
            print("=" * 80)
            
            for idx, (text, score) in enumerate(zip(texts, scores)):
                print(f"\n[Region {idx + 1}]")
                print(f"  ✅ Text: '{text}'")
                print(f"  ✅ Confidence: {score:.3f}")
                print(f"  Bbox: {polys[idx] if idx < len(polys) else 'N/A'}")
            
            print("\n" + "=" * 80)
            print("📊 SUMMARY")
            print("=" * 80)
            print(f"  ✅ Valid texts: {len([t for t in texts if t and t.strip()])}")
            print(f"  ⚠️  Empty texts: {len([t for t in texts if not t or not t.strip()])}")
            print(f"  📊 Total: {len(texts)}")
            
            if len([t for t in texts if t and t.strip()]) > 0:
                print("\n✅ OCR is working correctly!")
            else:
                print("\n🚨 PROBLEM: All texts are empty!")
            
            return
    
    # Original list format handling
    if not results or not results[0]:
        print("   ❌ No results returned!")
        return
    
    print(f"   Total detected regions: {len(results[0])}")
    print("\n" + "=" * 80)
    print("DETAILED RESULTS:")
    print("=" * 80)
    
    for idx, line in enumerate(results[0]):
        print(f"\n[Region {idx + 1}]")
        print(f"  Full structure: {line}")
        print(f"  Type: {type(line)}")
        print(f"  Length: {len(line) if isinstance(line, (list, tuple)) else 'N/A'}")
        
        if isinstance(line, (list, tuple)) and len(line) >= 2:
            bbox = line[0]
            text_info = line[1]
            
            print(f"  Bbox: {bbox}")
            print(f"  Text info: {text_info}")
            print(f"  Text info type: {type(text_info)}")
            
            if isinstance(text_info, (list, tuple)) and len(text_info) >= 2:
                text = text_info[0]
                conf = text_info[1]
                
                print(f"  ✅ Text: '{text}'")
                print(f"  ✅ Confidence: {conf:.3f}")
                print(f"  Text type: {type(text)}")
                print(f"  Text length: {len(str(text)) if text else 0}")
                print(f"  Text is empty: {not text or str(text).strip() == ''}")
            else:
                print(f"  ❌ Unexpected text_info format!")
    
    print("\n" + "=" * 80)
    print("📊 SUMMARY")
    print("=" * 80)
    
    valid_count = 0
    empty_count = 0
    error_count = 0
    
    for line in results[0]:
        try:
            if isinstance(line, (list, tuple)) and len(line) >= 2:
                text_info = line[1]
                if isinstance(text_info, (list, tuple)) and len(text_info) >= 2:
                    text = str(text_info[0]) if text_info[0] else ""
                    if text and text.strip():
                        valid_count += 1
                    else:
                        empty_count += 1
                else:
                    error_count += 1
            else:
                error_count += 1
        except:
            error_count += 1
    
    print(f"  ✅ Valid texts: {valid_count}")
    print(f"  ⚠️  Empty texts: {empty_count}")
    print(f"  ❌ Parse errors: {error_count}")
    print(f"  📊 Total: {len(results[0])}")
    
    if valid_count == 0:
        print("\n🚨 PROBLEM IDENTIFIED: All texts are empty or failed to parse!")
        print("   This explains the '400 Bad Request' error.")
    else:
        print("\n✅ OCR is working correctly!")
    
    print("\n" + "=" * 80)

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python debug_ocr.py <image_path>")
        print("Example: python debug_ocr.py bukti_tf.jpg")
        sys.exit(1)
    
    test_direct_ocr(sys.argv[1])
