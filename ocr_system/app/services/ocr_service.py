"""
=============================================
PaddleOCR Service Module
Text Detection (DBNet) & Recognition (CRNN/SVTR)
=============================================
"""

from paddleocr import PaddleOCR
import numpy as np
import cv2
from typing import List, Dict, Tuple, Optional
from loguru import logger
from app.core.config import settings
import time


class OCRService:
    """
    PaddleOCR Service untuk:
    1. Text Detection menggunakan DBNet
    2. Text Recognition menggunakan CRNN/SVTR
    3. Angle Classification
    """
    
    def __init__(self):
        """Initialize PaddleOCR (Compatible with PaddleOCR 3.3.1)"""
        logger.info("Initializing PaddleOCR 3.3.1...")
        
        try:
            # PaddleOCR 3.3.1 uses new parameter names
            ocr_params = {
                'use_textline_orientation': settings.ocr_use_angle_cls,
                'lang': settings.ocr_lang,
            }
            
            # Add custom model paths if specified (new parameter names)
            if settings.ocr_det_model_dir:
                ocr_params['text_detection_model_dir'] = settings.ocr_det_model_dir
            if settings.ocr_rec_model_dir:
                ocr_params['text_recognition_model_dir'] = settings.ocr_rec_model_dir
            if settings.ocr_cls_model_dir:
                ocr_params['textline_orientation_model_dir'] = settings.ocr_cls_model_dir
            
            self.ocr = PaddleOCR(**ocr_params)
            logger.info("✅ PaddleOCR 3.3.1 initialized successfully")
        except Exception as e:
            logger.error(f"❌ Failed to initialize PaddleOCR: {e}")
            raise
    
    def _validate_image_quality(self, image: np.ndarray) -> Tuple[bool, Optional[str]]:
        """
        Comprehensive image quality validation before OCR processing
        
        Requirements (when strict_quality_check=True):
        1. Minimum resolution: 1000x1000 pixels (configurable)
        2. Good lighting and contrast
        3. Sharp focus (not blurry)
        4. Not heavily compressed
        
        Returns:
            Tuple[is_valid, error_message]
        """
        try:
            # 1. Basic checks (always enforced)
            if image is None or image.size == 0:
                return False, "Invalid image: empty or None"
            
            height, width = image.shape[:2]
            
            # 2. Resolution check (configurable strictness)
            if settings.strict_quality_check:
                if height < settings.min_resolution or width < settings.min_resolution:
                    return False, (
                        f"Image resolution too low: {width}x{height}px. "
                        f"Minimum required: {settings.min_resolution}x{settings.min_resolution}px. "
                        f"Please use a higher resolution image for better OCR accuracy."
                    )
            else:
                # Relaxed mode for testing - just log warning
                if height < settings.min_resolution or width < settings.min_resolution:
                    logger.warning(
                        f"⚠️ Image resolution below recommended: {width}x{height}px "
                        f"(recommended: {settings.min_resolution}x{settings.min_resolution}px)"
                    )
            
            # 3. Blur detection using Laplacian variance
            # Convert to grayscale if needed
            if len(image.shape) == 3:
                gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
            else:
                gray = image
            
            # Calculate Laplacian variance (measure of focus/sharpness)
            laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()
            
            if settings.strict_quality_check:
                if laplacian_var < settings.min_sharpness:
                    return False, (
                        f"Image is too blurry (sharpness score: {laplacian_var:.2f}). "
                        f"Please upload a clearer, focused image. "
                        f"Ensure the image is not blurry and text is readable by human eye."
                    )
            else:
                if laplacian_var < settings.min_sharpness:
                    logger.warning(f"⚠️ Low sharpness detected: {laplacian_var:.2f} (recommended: >{settings.min_sharpness})")
            
            # 4. Contrast check
            contrast = gray.std()
            
            if settings.strict_quality_check:
                if contrast < settings.min_contrast:
                    return False, (
                        f"Image has poor contrast (score: {contrast:.2f}). "
                        f"Please ensure good lighting conditions. "
                        f"The image should have clear distinction between text and background."
                    )
            else:
                if contrast < settings.min_contrast:
                    logger.warning(f"⚠️ Low contrast detected: {contrast:.2f} (recommended: >{settings.min_contrast})")
            
            # 5. Check for over/under exposure
            mean_brightness = gray.mean()
            
            if settings.strict_quality_check:
                if mean_brightness < settings.min_brightness:
                    return False, (
                        "Image is too dark (underexposed). "
                        "Please use better lighting or increase image brightness."
                    )
                elif mean_brightness > settings.max_brightness:
                    return False, (
                        "Image is too bright (overexposed). "
                        "Please reduce lighting or adjust camera exposure."
                    )
            else:
                if mean_brightness < settings.min_brightness:
                    logger.warning(f"⚠️ Image too dark: {mean_brightness:.2f} (recommended: >{settings.min_brightness})")
                elif mean_brightness > settings.max_brightness:
                    logger.warning(f"⚠️ Image too bright: {mean_brightness:.2f} (recommended: <{settings.max_brightness})")
            
            # 6. Check dynamic range (avoid heavily compressed/degraded images)
            unique_values = len(np.unique(gray))
            
            if settings.strict_quality_check:
                if unique_values < settings.min_dynamic_range:
                    return False, (
                        f"Image appears heavily compressed or degraded (dynamic range: {unique_values}). "
                        f"Please upload original, uncompressed image. "
                        f"Avoid heavily compressed JPEG or low-quality screenshots."
                    )
            else:
                if unique_values < settings.min_dynamic_range:
                    logger.warning(f"⚠️ Low dynamic range: {unique_values} (recommended: >{settings.min_dynamic_range})")
            
            # All checks passed
            logger.info(
                f"✅ Image quality validation passed: "
                f"{width}x{height}px, sharpness: {laplacian_var:.2f}, "
                f"contrast: {contrast:.2f}, brightness: {mean_brightness:.2f}, "
                f"dynamic_range: {unique_values} "
                f"(strict_mode: {settings.strict_quality_check})"
            )
            return True, None
            
        except Exception as e:
            logger.error(f"Error during image quality validation: {e}")
            return False, f"Image quality validation failed: {str(e)}"
    
    def detect_and_recognize(self, image: np.ndarray) -> Dict:
        """
        Main OCR function - detect & recognize text
        
        Returns:
            dict dengan keys:
            - results: list of (bbox, (text, confidence))
            - raw_text: concatenated text
            - detection_confidence: avg detection score
            - recognition_confidence: avg recognition score
            - overall_confidence: combined score
            - processing_time_ms: waktu proses
        """
        start_time = time.time()
        
        try:
            # Comprehensive image quality validation
            is_valid, error_message = self._validate_image_quality(image)
            if not is_valid:
                logger.error(f"❌ Image quality validation failed: {error_message}")
                return self._empty_result(time.time() - start_time, error_message)
            
            height, width = image.shape[:2]
            logger.info(f"Image quality check passed: {width}x{height}px, channels: {image.shape[2] if len(image.shape) > 2 else 1}")
            
            # Run OCR (PaddleOCR 3.3.1 uses predict() method)
            logger.info(f"Running PaddleOCR 3.3.1 predict() on image with shape: {image.shape}")
            results = self.ocr.predict(image)
            
            logger.info(f"OCR raw results type: {type(results)}")
            logger.info(f"OCR results length: {len(results) if results else 0}")
            
            # PaddleOCR 3.3.1 returns list of OCRResult objects
            if not results or len(results) == 0:
                logger.warning("⚠️ No results returned from OCR")
                return self._empty_result(time.time() - start_time)
            
            # Extract first result (single page/image)
            ocr_result = results[0]
            logger.info(f"OCR result type: {type(ocr_result)}")
            
            # OCRResult is dict-like with keys: rec_texts, rec_scores, dt_polys, etc.
            if not hasattr(ocr_result, 'get') and not isinstance(ocr_result, dict):
                logger.error(f"Unexpected result format: {type(ocr_result)}")
                return self._empty_result(time.time() - start_time, "Unexpected OCR result format")
            
            # Extract text and scores from OCRResult
            rec_texts = ocr_result.get('rec_texts', ocr_result.get('rec_texts', []))
            rec_scores = ocr_result.get('rec_scores', ocr_result.get('rec_scores', []))
            dt_polys = ocr_result.get('dt_polys', ocr_result.get('rec_polys', []))
            
            logger.info(f"Extracted: {len(rec_texts)} texts, {len(rec_scores)} scores, {len(dt_polys)} polygons")
            
            if not rec_texts or len(rec_texts) == 0:
                logger.warning("⚠️ No text detected in image")
                return self._empty_result(time.time() - start_time)
            
            # Parse results with defensive programming
            parsed_results = []
            detection_scores = []
            recognition_scores = []
            raw_texts = []
            
            logger.info(f"Starting to parse {len(rec_texts)} OCR results...")
            
            for idx, (text, score) in enumerate(zip(rec_texts, rec_scores)):
                try:
                    logger.debug(f"Line {idx}: text='{text}', score={score}, type={type(text)}")
                    
                    # Convert to string and validate
                    text_str = str(text) if text else ""
                    conf = float(score) if score else 0.0
                    
                    # Skip empty text (but log it)
                    if not text_str or text_str.strip() == "":
                        logger.debug(f"Line {idx}: Skipping empty text")
                        continue
                    
                    logger.info(f"Line {idx}: ✅ Valid text: '{text_str}' (confidence: {conf:.3f})")
                    
                    # Get bounding box if available (convert numpy array to list for JSON serialization)
                    bbox = []
                    if idx < len(dt_polys):
                        bbox_raw = dt_polys[idx]
                        # Convert numpy array to list
                        if hasattr(bbox_raw, 'tolist'):
                            bbox = bbox_raw.tolist()
                        elif isinstance(bbox_raw, (list, tuple)):
                            bbox = list(bbox_raw)
                        else:
                            bbox = []
                    
                    parsed_results.append({
                        'bbox': bbox,
                        'text': text_str,
                        'confidence': conf
                    })
                    
                    recognition_scores.append(conf)
                    raw_texts.append(text_str)
                    
                    # Detection confidence (assume high if text recognized)
                    detection_scores.append(0.9)
                    
                except Exception as line_error:
                    logger.error(f"Line {idx}: Error parsing OCR line: {line_error}")
                    import traceback
                    logger.error(traceback.format_exc())
                    continue
            
            # Validate that we have actual results after parsing
            if not parsed_results or not raw_texts:
                logger.error("❌ No valid text extracted after parsing OCR results")
                logger.error(f"📊 Stats: Raw OCR results: {len(results[0])} items")
                logger.error(f"📊 Stats: Parsed results: {len(parsed_results)} items")
                logger.error(f"📊 Stats: Raw texts: {len(raw_texts)} items")
                logger.error(f"🔍 Debugging: Full raw OCR results structure:")
                for idx, item in enumerate(results[0][:5]):  # Show first 5 for debugging
                    logger.error(f"  Item {idx}: {item}")
                return self._empty_result(time.time() - start_time, "Text detected but failed validation. Check server logs for details.")
            
            # Calculate average scores
            avg_detection = np.mean(detection_scores) if detection_scores else 0.0
            avg_recognition = np.mean(recognition_scores) if recognition_scores else 0.0
            overall_confidence = (avg_detection * 0.3 + avg_recognition * 0.7)  # Weighted average
            
            processing_time = (time.time() - start_time) * 1000  # Convert to ms
            
            logger.info(f"OCR complete. Detected {len(parsed_results)} text regions. "
                       f"Overall confidence: {overall_confidence:.3f}")
            
            return {
                'results': parsed_results,
                'raw_text': '\n'.join(raw_texts),
                'detection_confidence': float(avg_detection),
                'recognition_confidence': float(avg_recognition),
                'overall_confidence': float(overall_confidence),
                'processing_time_ms': float(processing_time),
                'num_regions': len(parsed_results)
            }
            
        except Exception as e:
            logger.error(f"OCR processing error: {e}")
            return self._empty_result(time.time() - start_time, str(e))
    
    def _empty_result(self, elapsed_time: float, error: Optional[str] = None) -> Dict:
        """Return empty result structure"""
        return {
            'results': [],
            'raw_text': '',
            'detection_confidence': 0.0,
            'recognition_confidence': 0.0,
            'overall_confidence': 0.0,
            'processing_time_ms': elapsed_time * 1000,
            'num_regions': 0,
            'error': error
        }
    
    def detect_only(self, image: np.ndarray) -> List[np.ndarray]:
        """
        Text detection only (DBNet)
        Returns list of bounding boxes
        """
        try:
            # Use detection model only
            results = self.ocr.ocr(image, rec=False)
            
            if not results or not results[0]:
                return []
            
            bboxes = [line[0] for line in results[0]]
            logger.info(f"Detected {len(bboxes)} text regions")
            return bboxes
            
        except Exception as e:
            logger.error(f"Detection error: {e}")
            return []
    
    def recognize_region(self, image: np.ndarray, bbox: np.ndarray) -> Tuple[str, float]:
        """
        Recognize text in specific region (CRNN/SVTR)
        
        Args:
            image: full image
            bbox: bounding box [[x1,y1], [x2,y2], [x3,y3], [x4,y4]]
        
        Returns:
            (text, confidence)
        """
        try:
            # Crop region
            points = np.array(bbox, dtype=np.int32)
            x_min = int(min(points[:, 0]))
            x_max = int(max(points[:, 0]))
            y_min = int(min(points[:, 1]))
            y_max = int(max(points[:, 1]))
            
            cropped = image[y_min:y_max, x_min:x_max]
            
            # Recognize
            result = self.ocr.ocr(cropped, det=False)
            
            if result and result[0]:
                text, confidence = result[0][0]
                return text, confidence
            
            return "", 0.0
            
        except Exception as e:
            logger.error(f"Recognition error: {e}")
            return "", 0.0
    
    def batch_process(self, images: List[np.ndarray]) -> List[Dict]:
        """
        Process multiple images
        """
        results = []
        for idx, image in enumerate(images):
            logger.info(f"Processing image {idx + 1}/{len(images)}")
            result = self.detect_and_recognize(image)
            results.append(result)
        return results
    
    def get_model_info(self) -> Dict:
        """Get information about loaded models"""
        return {
            'lang': settings.ocr_lang,
            'use_gpu': settings.ocr_use_gpu,
            'use_angle_cls': settings.ocr_use_angle_cls,
            'det_model': settings.ocr_det_model_dir if hasattr(settings, 'ocr_det_model_dir') else 'default',
            'rec_model': settings.ocr_rec_model_dir if hasattr(settings, 'ocr_rec_model_dir') else 'default',
        }


# Singleton instance
_ocr_service_instance = None


def get_ocr_service() -> OCRService:
    """Get singleton OCR service instance"""
    global _ocr_service_instance
    if _ocr_service_instance is None:
        _ocr_service_instance = OCRService()
    return _ocr_service_instance
