-- تحديث enum في processing_history لإضافة improve_format
ALTER TABLE processing_history 
MODIFY COLUMN processing_type ENUM(
    'enhance',
    'translate', 
    'summarize',
    'improve_formatting',
    'improve_format',
    'extract_book_info',
    'enhance_translate',
    'enhance_summarize'
) NOT NULL; 