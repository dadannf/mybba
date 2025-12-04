#!/usr/bin/env python
"""
=============================================
Quick Start Script untuk OCR System
Install dependencies & initialize system
=============================================
"""

import subprocess
import sys
import os
from pathlib import Path


def print_step(step_num, message):
    """Print formatted step message"""
    print(f"\n{'='*60}")
    print(f"STEP {step_num}: {message}")
    print('='*60)


def run_command(command, shell=True):
    """Run command and return result"""
    try:
        result = subprocess.run(
            command,
            shell=shell,
            check=True,
            capture_output=True,
            text=True
        )
        return True, result.stdout
    except subprocess.CalledProcessError as e:
        return False, e.stderr


def main():
    print("""
    ╔═══════════════════════════════════════════════════════╗
    ║   OCR System Setup - Quick Start                      ║
    ║   Deep Learning OCR untuk Validasi Bukti Transfer    ║
    ╚═══════════════════════════════════════════════════════╝
    """)
    
    # Check Python version
    print_step(1, "Checking Python Version")
    if sys.version_info < (3, 8):
        print("❌ Error: Python 3.8+ required")
        sys.exit(1)
    print(f"✅ Python {sys.version.split()[0]} detected")
    
    # Install dependencies
    print_step(2, "Installing Dependencies")
    print("This may take a few minutes...")
    success, output = run_command(f"{sys.executable} -m pip install -r requirements.txt")
    if success:
        print("✅ Dependencies installed successfully")
    else:
        print(f"❌ Installation failed: {output}")
        sys.exit(1)
    
    # Create necessary directories
    print_step(3, "Creating Directories")
    directories = [
        'uploads',
        'logs',
        'models_pretrained/det',
        'models_pretrained/rec',
        'models_pretrained/cls',
        'fine_tuning/dataset/images',
        'fine_tuning/dataset/labels',
    ]
    
    for dir_path in directories:
        Path(dir_path).mkdir(parents=True, exist_ok=True)
        print(f"✅ Created: {dir_path}")
    
    # Setup .env file
    print_step(4, "Setting up Environment Variables")
    if not Path('.env').exists():
        if Path('.env.example').exists():
            import shutil
            shutil.copy('.env.example', '.env')
            print("✅ .env file created from .env.example")
            print("⚠️  Please edit .env file with your database credentials")
        else:
            print("❌ .env.example not found")
    else:
        print("✅ .env file already exists")
    
    # Initialize database
    print_step(5, "Initializing Database")
    print("Creating ocr_validations table...")
    success, output = run_command(
        f'{sys.executable} -c "from app.core.database import init_db; init_db()"'
    )
    if success:
        print("✅ Database initialized successfully")
    else:
        print(f"⚠️  Database initialization warning: {output}")
        print("You may need to create the table manually or check database connection")
    
    # Test imports
    print_step(6, "Testing Imports")
    test_imports = [
        "from app.core.config import settings",
        "from app.services.ocr_service import OCRService",
        "from app.utils.preprocessing import ImagePreprocessor",
        "from app.utils.text_parser import BankTransferParser",
        "from app.services.validation_service import ValidationService",
    ]
    
    for imp in test_imports:
        try:
            exec(imp)
            print(f"✅ {imp}")
        except Exception as e:
            print(f"❌ {imp}: {e}")
    
    # Download PaddleOCR models (will happen on first run)
    print_step(7, "PaddleOCR Models")
    print("ℹ️  PaddleOCR models will be downloaded automatically on first use")
    print("   This may take a few minutes on first run")
    
    # Summary
    print(f"\n{'='*60}")
    print("✅ Setup Complete!")
    print('='*60)
    print("\nNext Steps:")
    print("1. Edit .env file with your database credentials")
    print("2. Start the server:")
    print("   python main.py")
    print("\n3. Test the API:")
    print("   curl http://localhost:8000/")
    print("\n4. Upload a test image:")
    print("   Use the PHP integration or curl command")
    print("\nFor more information, see README_OCR.md")
    print('='*60)


if __name__ == "__main__":
    main()
