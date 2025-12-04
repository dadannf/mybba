"""
=============================================
Image Preprocessing Module
Image Enhancement, Denoising, Normalization
=============================================
"""

import cv2
import numpy as np
from PIL import Image
from typing import Tuple, Optional
import albumentations as A
from loguru import logger


class ImagePreprocessor:
    """
    Preprocessing pipeline untuk gambar bukti transfer
    - Resize & normalize
    - Denoising
    - Contrast enhancement
    - Rotation correction
    - Binarization
    """
    
    def __init__(self, max_size: int = 2048, min_size: int = 32):
        self.max_size = max_size
        self.min_size = min_size
        
        # Albumentations augmentation pipeline
        self.enhance_pipeline = A.Compose([
            A.CLAHE(clip_limit=2.0, tile_grid_size=(8, 8), p=0.8),
            A.Sharpen(alpha=(0.2, 0.5), lightness=(0.5, 1.0), p=0.5),
        ])
    
    def load_image(self, image_path: str) -> np.ndarray:
        """Load image dari file"""
        try:
            image = cv2.imread(image_path)
            if image is None:
                raise ValueError(f"Failed to load image: {image_path}")
            return cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        except Exception as e:
            logger.error(f"Error loading image: {e}")
            raise
    
    def resize_image(self, image: np.ndarray, max_size: Optional[int] = None) -> np.ndarray:
        """
        Resize image sambil maintain aspect ratio
        """
        max_size = max_size or self.max_size
        height, width = image.shape[:2]
        
        # Check if resize needed
        if max(height, width) <= max_size:
            return image
        
        # Calculate new dimensions
        if height > width:
            new_height = max_size
            new_width = int(width * (max_size / height))
        else:
            new_width = max_size
            new_height = int(height * (max_size / width))
        
        return cv2.resize(image, (new_width, new_height), interpolation=cv2.INTER_AREA)
    
    def denoise_image(self, image: np.ndarray) -> np.ndarray:
        """
        Remove noise menggunakan Non-local Means Denoising
        """
        return cv2.fastNlMeansDenoisingColored(image, None, 10, 10, 7, 21)
    
    def enhance_contrast(self, image: np.ndarray) -> np.ndarray:
        """
        Enhance contrast menggunakan CLAHE (Contrast Limited Adaptive Histogram Equalization)
        """
        # Convert to LAB color space
        lab = cv2.cvtColor(image, cv2.COLOR_RGB2LAB)
        l, a, b = cv2.split(lab)
        
        # Apply CLAHE to L channel
        clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8, 8))
        l = clahe.apply(l)
        
        # Merge channels
        lab = cv2.merge([l, a, b])
        return cv2.cvtColor(lab, cv2.COLOR_LAB2RGB)
    
    def correct_rotation(self, image: np.ndarray) -> Tuple[np.ndarray, float]:
        """
        Detect dan correct skew/rotation menggunakan Hough Transform
        """
        gray = cv2.cvtColor(image, cv2.COLOR_RGB2GRAY)
        
        # Edge detection
        edges = cv2.Canny(gray, 50, 150, apertureSize=3)
        
        # Detect lines
        lines = cv2.HoughLinesP(edges, 1, np.pi / 180, threshold=100,
                                minLineLength=100, maxLineGap=10)
        
        if lines is None:
            return image, 0.0
        
        # Calculate angles
        angles = []
        for line in lines:
            x1, y1, x2, y2 = line[0]
            angle = np.degrees(np.arctan2(y2 - y1, x2 - x1))
            angles.append(angle)
        
        # Get median angle
        median_angle = np.median(angles) if angles else 0
        
        # Rotate if angle is significant
        if abs(median_angle) > 0.5:
            height, width = image.shape[:2]
            center = (width // 2, height // 2)
            rotation_matrix = cv2.getRotationMatrix2D(center, median_angle, 1.0)
            rotated = cv2.warpAffine(image, rotation_matrix, (width, height),
                                     flags=cv2.INTER_CUBIC,
                                     borderMode=cv2.BORDER_REPLICATE)
            return rotated, median_angle
        
        return image, 0.0
    
    def binarize_image(self, image: np.ndarray, method: str = "otsu") -> np.ndarray:
        """
        Binarization untuk better text detection
        Methods: otsu, adaptive, sauvola
        """
        gray = cv2.cvtColor(image, cv2.COLOR_RGB2GRAY)
        
        if method == "otsu":
            _, binary = cv2.threshold(gray, 0, 255, cv2.THRESHOLD_BINARY + cv2.THRESHOLD_OTSU)
        elif method == "adaptive":
            binary = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                          cv2.THRESH_BINARY, 11, 2)
        else:  # default otsu
            _, binary = cv2.threshold(gray, 0, 255, cv2.THRESHOLD_BINARY + cv2.THRESHOLD_OTSU)
        
        # Convert back to RGB
        return cv2.cvtColor(binary, cv2.COLOR_GRAY2RGB)
    
    def remove_shadows(self, image: np.ndarray) -> np.ndarray:
        """
        Remove shadows dari gambar
        """
        rgb_planes = cv2.split(image)
        result_planes = []
        
        for plane in rgb_planes:
            dilated = cv2.dilate(plane, np.ones((7, 7), np.uint8))
            bg_img = cv2.medianBlur(dilated, 21)
            diff_img = 255 - cv2.absdiff(plane, bg_img)
            norm_img = cv2.normalize(diff_img, None, alpha=0, beta=255,
                                    norm_type=cv2.NORM_MINMAX, dtype=cv2.CV_8UC1)
            result_planes.append(norm_img)
        
        return cv2.merge(result_planes)
    
    def preprocess(self, image_path: str, 
                   apply_denoise: bool = True,
                   apply_contrast: bool = True,
                   apply_rotation: bool = True,
                   apply_shadow_removal: bool = False) -> dict:
        """
        Full preprocessing pipeline
        
        Returns:
            dict dengan keys: original, processed, rotation_angle, metadata
        """
        logger.info(f"Starting preprocessing for: {image_path}")
        
        try:
            # Load image
            image = self.load_image(image_path)
            if image is None:
                raise ValueError("Failed to load image - returned None")
            
            original = image.copy()
            
            # Resize
            image = self.resize_image(image)
            
            # Remove shadows (optional, bisa lambat)
            if apply_shadow_removal:
                try:
                    image = self.remove_shadows(image)
                except Exception as e:
                    logger.warning(f"Shadow removal failed: {e}, continuing without it")
            
            # Denoise
            if apply_denoise:
                try:
                    image = self.denoise_image(image)
                except Exception as e:
                    logger.warning(f"Denoise failed: {e}, continuing without it")
            
            # Enhance contrast
            if apply_contrast:
                try:
                    image = self.enhance_contrast(image)
                except Exception as e:
                    logger.warning(f"Contrast enhancement failed: {e}, continuing without it")
            
            # Correct rotation
            rotation_angle = 0.0
            if apply_rotation:
                try:
                    image, rotation_angle = self.correct_rotation(image)
                except Exception as e:
                    logger.warning(f"Rotation correction failed: {e}, continuing without it")
            
            # Apply albumentations enhancement
            try:
                augmented = self.enhance_pipeline(image=image)
                image = augmented['image']
            except Exception as e:
                logger.warning(f"Albumentations enhancement failed: {e}, using un-augmented image")
            
            logger.info(f"Preprocessing complete. Rotation: {rotation_angle:.2f}°")
            
            return {
                'original': original,
                'processed': image,
                'rotation_angle': rotation_angle,
                'metadata': {
                    'original_shape': original.shape,
                    'processed_shape': image.shape,
                    'rotation_corrected': rotation_angle != 0.0
                }
            }
            
        except Exception as e:
            logger.error(f"Preprocessing failed: {e}")
            raise
