import os
import re

files_to_edit = [
    "api/service/ActiveNumber2.php",
    "api/service/buynumber2.php",
    "api/service/cancelNumber2.php",
    "api/service/getCountriesForService2.php",
    "api/service/getMessage2.php",
    "api/service/getService2.php",
    "api/service/getServices2All.php"
]

hardcoded_block = """
$api_url = "https://5sim.net";
$api_key = "eyJhbGciOiJSUzUxMiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE4MjA2ODU5OTksImlhdCI6MTc4OTE0OTk5OSwicmF5IjoiYmMxNTVkYzI1NGNkZjlhZThlYTg3OTFjN2Y1MzYwNGEiLCJzdWIiOjQ0ODMyNjV9.PxCFWUR6bP29BpMt1PKfAdHSmdmXUQriKLq6nPYEkWldyephtuijh4BqnU_EtMTgxXdLXwmX-hNJKKBNEMZBG-p8WK1o6usLPOdTwWu3Lw0yOcS0e-YwPUxTPKu0ocZSdSP5FJtCUZMTKCTIe7nZmWBngLkyUmuPQzbNG12KF5JL0G6_G8_iG3WBQSMg7yeQF-13l6KOzc5aA56V4PdgiVHlTYbibicINth7evneW7I7pT_HLStvbUjLtgA8mEWsSvUFMEknyflUkwZi2Yo2sjSOEZH50Tc5KYz7iKFIq7p5KKmf3J_5pM7PFri1I8yXpSqUeEjUoGtN4QnDRCSRmg";
"""

for file_path in files_to_edit:
    if not os.path.exists(file_path): continue
    with open(file_path, "r", encoding="utf-8") as f:
        content = f.read()

    # Disable require_api_active
    content = content.replace("require_api_active($conn, 2);", "// require_api_active($conn, 2);")
    
    # We want to replace the DB fetch lines with hardcoded strings.
    # Pattern 1: $api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'"); ... $api_url = rtrim(...);
    # Pattern 2: $sql4 = mysqli_query(...); ... $api_key = ...; $api_url = ...;
    # Since I don't want to mess up, I'll use a very specific regex for each.
    
    # Let's match the mysqli_query line down to the extraction lines.
    pattern = re.compile(r'\$[a-zA-Z0-9_]+\s*=\s*mysqli_query\(\$conn,\s*"SELECT \* FROM api_detail WHERE id=\'2\'"\).*?(?:\$api_url\s*=\s*(?:rtrim)?\([^;]+;|\$api_key\s*=\s*[^;]+;)+', re.DOTALL)
    
    new_content = pattern.sub(hardcoded_block.strip(), content)
    
    # There's also the error handling we just added in getServices2All.php
    # Let's clean up any dangling if (!$api_sql) { ... }
    pattern2 = re.compile(r'if\s*\(!\$api_sql\)\s*\{\s*echo json_encode.*?\s*exit;\s*\}', re.DOTALL)
    new_content = pattern2.sub('', new_content)
    
    pattern3 = re.compile(r'\$api_data\s*=\s*mysqli_fetch_assoc\([^;]+;\s*', re.DOTALL)
    new_content = pattern3.sub('', new_content)
    
    # For buynumber2, the block is:
    # $sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
    # $api_data = mysqli_fetch_assoc($sql4);
    # $api_key = $api_data['api_key'];
    # $api_url = rtrim($api_data['api_url'], '/');
    
    with open(file_path, "w", encoding="utf-8") as f:
        f.write(new_content)
