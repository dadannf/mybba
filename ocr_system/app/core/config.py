"""
=============================================
Core Configuration Module
Pydantic Settings untuk Environment Variables
=============================================
"""

from pydantic_settings import BaseSettings
from typing import List, Optional
from functools import lru_cache


class Settings(BaseSettings):
    """Application Settings"""
    
    # FastAPI
    api_host: str = "0.0.0.0"
    api_port: int = 8000
    api_workers: int = 4
    debug: bool = True
    
    # Database
    db_host: str = "localhost"
    db_port: int = 3306
    db_name: str = "dbsekolah"
    db_user: str = "root"
    db_password: str = ""
    db_charset: str = "utf8mb4"
    
    # PaddleOCR (Compatible with PaddleOCR 3.3.1+)
    ocr_lang: str = "en"  # Use 'en' for Latin alphabet (supports Indonesian)
    ocr_use_angle_cls: bool = True  # Converted to use_textline_orientation
    ocr_det_model_dir: Optional[str] = None  # text_detection_model_dir
    ocr_rec_model_dir: Optional[str] = None  # text_recognition_model_dir
    ocr_cls_model_dir: Optional[str] = None  # textline_orientation_model_dir
    
    # Image Processing
    max_image_size: int = 4096
    min_image_size: int = 32
    allowed_extensions: str = "jpg,jpeg,png,bmp,tiff"
    max_file_size_mb: int = 10
    
    # Image Quality Validation (NEW)
    strict_quality_check: bool = False  # Set True for production, False for testing
    min_resolution: int = 1000  # Minimum width/height in pixels
    min_sharpness: float = 100.0  # Laplacian variance threshold
    min_contrast: float = 30.0  # Minimum std deviation
    min_brightness: float = 30.0  # Avoid underexposed images
    max_brightness: float = 225.0  # Avoid overexposed images
    min_dynamic_range: int = 50  # Minimum unique gray levels
    
    # Validation Thresholds
    min_confidence_score: float = 0.70
    min_text_detection_score: float = 0.60
    min_recognition_score: float = 0.75
    min_amount_match_threshold: float = 0.95
    
    # Upload Settings
    upload_dir: str = "./uploads"
    temp_dir: str = "./temp"
    
    # Logging
    log_level: str = "DEBUG"  # Changed to DEBUG for detailed troubleshooting
    log_file: str = "./logs/ocr_system.log"
    log_rotation: str = "10 MB"
    log_retention: str = "30 days"
    
    # Security
    secret_key: str = "your-secret-key-change-in-production"
    # CORS - allow multiple origins including ngrok
    allowed_origins: str = "http://localhost:8080,http://mybba.test:8080,https://*.ngrok-free.app,https://*.ngrok.io"
    
    class Config:
        env_file = ".env"
        case_sensitive = False
    
    @property
    def database_url(self) -> str:
        """Generate database URL"""
        return f"mysql+pymysql://{self.db_user}:{self.db_password}@{self.db_host}:{self.db_port}/{self.db_name}?charset={self.db_charset}"
    
    @property
    def allowed_extensions_list(self) -> List[str]:
        """Get allowed extensions as list"""
        return [ext.strip() for ext in self.allowed_extensions.split(",")]
    
    @property
    def allowed_origins_list(self) -> List[str]:
        """Get allowed CORS origins as list"""
        return [origin.strip() for origin in self.allowed_origins.split(",")]


@lru_cache()
def get_settings() -> Settings:
    """Get cached settings instance"""
    return Settings()


# Export settings instance
settings = get_settings()
