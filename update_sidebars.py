import os
import glob
import re

files = glob.glob('admin/*.php')
for f in files:
    if f.endswith('content_submissions.php') or f.endswith('review_content.php'):
        continue
    
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
        
    if 'content_submissions.php' not in content:
        # We replace the line containing submissions.php
        # Find <li><a href="submissions.php" ...
        pattern = r'(<li><a href="submissions\.php".*?</li>)'
        replacement = r'<li><a href="content_submissions.php" class="sidebar-link"><i class="fa-solid fa-file-signature" style="margin-right: 0.5rem;"></i> Content Submissions</a></li>\n                \1'
        
        new_content = re.sub(pattern, replacement, content)
        
        if new_content != content:
            with open(f, 'w', encoding='utf-8') as file:
                file.write(new_content)
            print(f"Updated {f}")
