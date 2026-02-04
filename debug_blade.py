
import re
import sys

filename = 'resources/views/admin-views/order/order-view.blade.php'

try:
    with open(filename, 'r', encoding='utf-8') as f:
        lines = f.readlines()
except FileNotFoundError:
    print(f"File not found: {filename}")
    sys.exit(1)

stack = []

# Map checking
match_map = {
    '@endif': ['@if', '@isset', '@empty', '@auth', '@guest', '@can', '@cannot'],
    '@endforeach': ['@foreach'],
    '@endfor': ['@for'],
    '@endwhile': ['@while'],
    '@endswitch': ['@switch'],
    '@endpush': ['@push'],
    '@endsection': ['@section'],
    '@endcan': ['@can'],
    '@enderror': ['@error']
}

for i, line in enumerate(lines):
    line_num = i + 1
    # Remove blade comments
    line = re.sub(r'{{--.*?--}}', '', line)
    
    # Find all directives in the line
    # We want to process them in order they appear in the line
    
    # Regex for start tags
    starts = [m for m in re.finditer(r'@(if|foreach|for|while|push|section|empty|auth|guest|isset|switch|can|error)(?![a-zA-Z])', line)]
    # Regex for end tags
    ends = [m for m in re.finditer(r'@(endif|endforeach|endfor|endwhile|endpush|endsection|endempty|endauth|endguest|endisset|endswitch|endcan|enderror)', line)]
    
    # Combine and sort by position
    all_matches = []
    for m in starts:
        all_matches.append((m.start(), m.group(0), 'start'))
    for m in ends:
        all_matches.append((m.start(), m.group(0), 'end'))
        
    all_matches.sort(key=lambda x: x[0])
    
    for pos, directive, type in all_matches:
        if type == 'start':
            stack.append((directive, line_num))
        else:
            if not stack:
                print(f"Error: Unexpected {directive} at line {line_num}")
            else:
                last, last_line = stack.pop()
                expected_starts = match_map.get(directive, [])
                if last not in expected_starts:
                     print(f"Mismatch: Found {directive} at {line_num} but stack had {last} from {line_num} (opened at {last_line})")

if stack:
    print(f"Error: Unclosed directives: {stack}")
else:
    print("All directives balanced.")
