# Laravel AI Books Manager

## Overview

A comprehensive system for managing books and processing texts using artificial intelligence. Supports text extraction from PDF files and processing using Google Gemini AI.

## Key Features

### 📚 Book Management
- Upload and manage PDF files
- Extract text from PDF using multi-language OCR
- Manage text files (split, merge, reorder)

### 🤖 AI Processor
- **Text Enhancement**: Improve text quality and fluency
- **Translation**: Translate text to 17 supported languages
- **Summarization**: Summarize text while preserving main ideas
- **Language Improvement**: Correct grammatical and spelling errors
- **Formatting Improvement**: Improve text formatting and organization
- **Book Information Extraction**: Extract book title, author, and summary

### 🌍 Supported Languages
- Arabic, English, French, Spanish
- German, Italian, Portuguese, Russian
- Chinese, Japanese, Korean, Turkish
- Persian, Urdu, Hindi, Bengali

## Installation and Setup

### Prerequisites
- PHP 8.1+
- Laravel 10+
- MySQL 8.0+
- Python 3.8+ (for processing)
- Tesseract OCR

### Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Python dependencies
pip install -r scripts/requirements.txt

# Install Tesseract OCR
# Ubuntu/Debian
sudo apt-get install tesseract-ocr tesseract-ocr-ara tesseract-ocr-eng tesseract-ocr-fra

# Windows
# Download Tesseract from https://github.com/UB-Mannheim/tesseract/wiki
```

### Database Setup

```bash
# Create database
php artisan migrate

# Add default prompts
php artisan ai:seed-prompts

# Download language files for Tesseract
php artisan download:language-files
```

### Environment Variables

Add the following variables to your `.env` file:

```env
# Database settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_books_manager
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Google Gemini AI settings
GEMINI_API_KEY=your_gemini_api_key
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent

# Application settings
APP_NAME="AI Books Manager"
APP_URL=http://localhost:8000
```

### Run Application

```bash
# Start server
php artisan serve

# Access admin panel
# http://localhost:8000/admin
```

## Usage

### 1. Upload PDF File
1. Go to "Upload Files" in admin panel
2. Upload a PDF file
3. Click "Extract Text" to extract text from PDF

### 2. Manage Text Files
1. Go to "File Management" in admin panel
2. Select the text file to manage
3. Click "Manage Files" to access editing tools

### 3. AI Text Processing
1. From file management page, click "AI Processing"
2. Select files to process
3. Choose processing type (enhance, translate, summarize, etc.)
4. Select target language
5. Click "Start Processing"

### 4. Manage Prompts
1. Go to "AI Prompts" in admin panel
2. You can add or modify prompts as needed
3. Prompts determine how texts are processed

## Available Commands

### AI Commands
```bash
# Add default prompts
php artisan ai:seed-prompts

# Test AI processor
php artisan ai:test "Text to process" --type=enhance --language=Arabic
```

### Language Management Commands
```bash
# Download language files for Tesseract
php artisan download:language-files
```

## Project Structure

```
laravel-ai-books-manager/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   ├── Filament/Resources/        # Filament resources
│   ├── Http/Controllers/          # Controllers
│   ├── Models/                    # Models
│   └── Services/                  # Services
├── database/
│   ├── migrations/                # Migration files
│   └── ai_books_manager.sql      # Database
├── resources/
│   └── views/                     # Blade templates
├── scripts/                       # Python scripts
│   ├── simple_extract.py         # Text extraction
│   └── requirements.txt          # Python dependencies
└── routes/
    └── web.php                   # Web routes
```

## Main Tables

- `books`: Basic books
- `file_managers`: File management
- `ai_prompts`: AI prompts
- `enhanced_texts`: Enhanced texts
- `translated_texts`: Translated texts
- `summarized_texts`: Summarized texts
- `language_improved_texts`: Language improved texts
- `formatting_improved_texts`: Formatting improved texts
- `processing_history`: Processing history

## Contributing

1. Fork the project
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Create Pull Request

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.

## Support

For support and inquiries, please contact via:
- Create an Issue on GitHub
- Send email to: hsnwww@gmail.com