# AI Books Manager - Laravel

## Overview

A comprehensive system for managing and processing books using artificial intelligence. The system enables text extraction from PDF files, content enhancement, translation, summarization, and creation of professional blog articles.

## Key Features

### 1. PDF Processing
- High-accuracy text extraction from PDF files
- Preservation of original formatting
- Support for large files (up to 50MB)

### 2. AI Processing
- **Text Enhancement**: Improve text quality and organization
- **Text Translation**: Translate to 16 different languages
- **Text Summarization**: Create concise and useful summaries
- **Bullet Points Summary**: Organize content in clear points
- **Book Information Extraction**: Extract title, author, and summary
- **Blog Article Creation**: Write professional blog articles

### 3. Content Management
- **Blog Articles**: Create and manage professional articles
- **Article Types**: Blog posts, book reviews, summaries, analyses, study guides
- **Status Management**: Draft, published, archived
- **SEO Keywords**: Automatic keyword extraction
- **Article Statistics**: Word count, processing date

### 4. File Management
- Upload PDF files
- Extract texts
- Organize files in folders
- Merge and split files
- Reorder chapters

### 5. Multi-Language Support
- Arabic, English, French, Spanish
- German, Italian, Portuguese, Russian
- Chinese, Japanese, Korean, Turkish
- Persian, Urdu, Hindi, Bengali

## Technologies Used

- **Laravel 11**: Main framework
- **Filament 3**: Advanced admin panel
- **Google Gemini AI**: AI processing
- **MySQL**: Database
- **Tailwind CSS**: UI design
- **Alpine.js**: Frontend interactivity

## Installation and Setup

### Requirements
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+

### Installation Steps

1. **Clone the project**
```bash
git clone https://github.com/your-repo/laravel-ai-books-manager.git
cd laravel-ai-books-manager
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database configuration**
```bash
# Edit .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_books_manager
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Build assets**
```bash
npm run build
```

7. **Start server**
```bash
php artisan serve
```

## Google Gemini AI Setup

1. Get API key from [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Add the key to `.env` file:
```
GOOGLE_GEMINI_API_KEY=your_api_key_here
```

## Usage

### 1. Upload Books
- Go to "File Management" > "Upload Files"
- Select PDF file
- Wait for text extraction to complete

### 2. AI Processing
- Go to "AI Processor"
- Select files to process
- Choose processing type:
  - **Book Information Extraction**: Get title, author, and summary
  - **Text Summarization**: Create concise summary
  - **Text Translation**: Translate text to another language
  - **Text Enhancement**: Improve text quality
  - **Bullet Points Summary**: Organize content in points
  - **Professional Blog Article**: Create professional blog article

### 3. Blog Articles Management
- Go to "Content Management" > "Blog Articles"
- View all created articles
- Edit status (draft/published/archived)
- Edit content and SEO keywords
- Manage different article types

### 4. Processed Texts Management
- View enhanced, translated, and summarized texts
- Download texts as text files
- Delete unwanted texts

## Database Structure

### Main Tables
- `books`: Basic book information
- `books_info`: Detailed book information
- `blog_articles`: Blog articles
- `enhanced_texts`: Enhanced texts
- `translated_texts`: Translated texts
- `summarized_texts`: Summarized texts
- `formatting_improved_texts`: Formatted texts
- `processing_histories`: Processing history
- `ai_prompts`: AI prompts

## API Endpoints

### AI Processing
- `POST /ai-processor`: Process texts with AI
- `GET /ai-processor/{bookId}`: Show AI processor for specific book

### AI Trial
- `GET /ai-trial`: AI trial page
- `POST /ai-trial/process`: Process trial text

## Security

- User authentication
- CSRF protection
- Data encryption
- File upload validation
- File size restrictions

## Performance

- Query caching
- File compression
- Parallel processing for large operations
- Database query optimization

## Support and Contribution

### Bug Reports
- Use GitHub Issues to report bugs
- Provide complete details about the issue
- Include screenshots if possible

### Contributing to Development
1. Fork the project
2. Create a new branch for the feature
3. Write tests for new features
4. Follow coding standards
5. Send Pull Request

## License

This project is licensed under the MIT License. See LICENSE file for details.

## Future Updates

- Support for more AI languages
- Improved keyword extraction algorithms
- Advanced text analysis features
- Support for more file types
- UI improvements
- Complete RESTful API 