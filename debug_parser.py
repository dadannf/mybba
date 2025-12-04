"""
Debug parser - cek kenapa amount tidak terdeteksi
"""
import re

# Simulasi raw text dari OCR
raw_text = """X
Transaksi
DANA
DANA
DANA
23 Des 2021 · 13:09
DANA ID 0896..5260
DANA
Transaksi berhasil!
Kirim Uang Rp200.000 ke AHMAD HILMI FAUZAN -
DANA
BCA...2811
KIRIM UANG
DANA
Total Bayar
Rp200.000
DANA
Metode Pembayaran
Saldo DANA"""

print("\n" + "="*80)
print("🔍 DEBUG PARSER - AMOUNT EXTRACTION")
print("="*80)

# Test patterns
patterns = [
    ("E-Wallet: Total Bayar", r'(?i)(?:total\s+bayar|total\s+pembayaran)[\s:]*(?:rp\.?)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*)'),
    ("E-Wallet: Kirim Uang", r'(?i)kirim\s+uang\s+(?:rp\.?)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*)'),
    ("Traditional: Rp pattern", r'(?i)(?:rp\.?|idr\.?|rupiah)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?)'),
]

print(f"\n📄 RAW TEXT:")
print(raw_text)
print(f"\n🔍 Testing Patterns:")
print("-" * 80)

for name, pattern in patterns:
    print(f"\n{name}:")
    print(f"   Pattern: {pattern}")
    matches = re.finditer(pattern, raw_text)
    found = False
    for match in matches:
        found = True
        amount_str = match.group(1) if match.lastindex else match.group(0)
        print(f"   ✅ MATCH: '{match.group(0)}' → Amount: '{amount_str}'")
    if not found:
        print(f"   ❌ NO MATCH")

print("\n" + "="*80)
print("🔧 SOLUSI:")
print("="*80)

# Test dengan newline handling
print(f"\nTest dengan multiline mode:")
pattern_multiline = r'(?im)(?:total\s+bayar)[\s\n:]*(?:rp\.?)?\s*([0-9]{1,3}(?:[.,][0-9]{3})*)'
matches = re.finditer(pattern_multiline, raw_text)
for match in matches:
    amount_str = match.group(1)
    print(f"✅ MATCH: '{match.group(0).replace(chr(10), ' ')}' → Amount: '{amount_str}'")

print("\n" + "="*80)
