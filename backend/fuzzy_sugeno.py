"""
Fuzzy Inference System (FIS) - Metode Sugeno Orde-Nol

Dua input:
  1. Suhu Ruangan  (15°C - 40°C) → Dingin / Normal / Panas
  2. Beban CPU      (0% - 100%)   → Rendah / Sedang / Tinggi

Satu output (konstanta Sugeno):
  Target Suhu AC (16°C - 28°C)

9 Aturan (3x3 kombinasi), defuzzifikasi menggunakan Weighted Average.
"""

from config import TEMP_MIN, TEMP_MAX, CPU_MIN, CPU_MAX, AC_MIN, AC_MAX


def fuzzy_temperature(temp: float) -> dict:
    """
    Fuzzifikasi input suhu ruangan.
    Mengembalikan derajat keanggotaan untuk 3 himpunan: dingin, normal, panas.
    """
    dingin = 0.0
    normal = 0.0
    panas = 0.0

    if temp <= 20:
        dingin = 1.0
    elif 20 < temp <= 25:
        dingin = (25 - temp) / 5.0

    if 22 <= temp <= 25:
        normal = (temp - 22) / 3.0
    elif 25 < temp <= 28:
        normal = (28 - temp) / 3.0

    if temp >= 28:
        panas = 1.0
    elif 25 <= temp < 28:
        panas = (temp - 25) / 3.0

    return {"dingin": dingin, "normal": normal, "panas": panas}


def fuzzy_cpu_load(cpu: float) -> dict:
    """
    Fuzzifikasi input beban CPU.
    Mengembalikan derajat keanggotaan untuk 3 himpunan: rendah, sedang, tinggi.
    """
    rendah = 0.0
    sedang = 0.0
    tinggi = 0.0

    if cpu <= 25:
        rendah = 1.0
    elif 25 < cpu <= 40:
        rendah = (40 - cpu) / 15.0

    if 30 <= cpu <= 50:
        sedang = (cpu - 30) / 20.0
    elif 50 < cpu <= 70:
        sedang = (70 - cpu) / 20.0

    if cpu >= 75:
        tinggi = 1.0
    elif 60 <= cpu < 75:
        tinggi = (cpu - 60) / 15.0

    return {"rendah": rendah, "sedang": sedang, "tinggi": tinggi}


def evaluate_rules(temp_fuzzy: dict, cpu_fuzzy: dict) -> list:
    """
    Evaluasi aturan (rule evaluation).
    Operator AND menggunakan fungsi min().
    Mengembalikan list of tuple (fire_strength, sugo_const).
    """
    rules = []
    t = temp_fuzzy
    c = cpu_fuzzy

    # R1: IF temp=dingin AND cpu=rendah THEN ac=28°C
    rules.append((min(t["dingin"], c["rendah"]), 28.0))
    # R2: IF temp=dingin AND cpu=sedang THEN ac=26°C
    rules.append((min(t["dingin"], c["sedang"]), 26.0))
    # R3: IF temp=dingin AND cpu=tinggi THEN ac=24°C
    rules.append((min(t["dingin"], c["tinggi"]), 24.0))

    # R4: IF temp=normal AND cpu=rendah THEN ac=26°C
    rules.append((min(t["normal"], c["rendah"]), 26.0))
    # R5: IF temp=normal AND cpu=sedang THEN ac=22°C
    rules.append((min(t["normal"], c["sedang"]), 22.0))
    # R6: IF temp=normal AND cpu=tinggi THEN ac=20°C
    rules.append((min(t["normal"], c["tinggi"]), 20.0))

    # R7: IF temp=panas AND cpu=rendah THEN ac=22°C
    rules.append((min(t["panas"], c["rendah"]), 22.0))
    # R8: IF temp=panas AND cpu=sedang THEN ac=18°C
    rules.append((min(t["panas"], c["sedang"]), 18.0))
    # R9: IF temp=panas AND cpu=tinggi THEN ac=16°C
    rules.append((min(t["panas"], c["tinggi"]), 16.0))

    return rules


def defuzzify_sugeno(rules: list) -> float:
    """
    Defuzzifikasi Sugeno Orde-Nol menggunakan Weighted Average.
    Formula: z* = Σ(α_i × z_i) / Σ(α_i)
    """
    numerator = 0.0
    denominator = 0.0

    for fire_strength, z_value in rules:
        numerator += fire_strength * z_value
        denominator += fire_strength

    if denominator == 0.0:
        return (AC_MIN + AC_MAX) / 2.0  # fallback jika tidak ada rule aktif

    return round(numerator / denominator, 2)


def compute_ac_target(temp: float, cpu_load: float) -> float:
    """
    Fungsi utama FIS Sugeno.
    Menerima nilai crisp suhu & beban CPU, mengembalikan target suhu AC.
    """
    temp_fuzzy = fuzzy_temperature(temp)
    cpu_fuzzy = fuzzy_cpu_load(cpu_load)
    rules = evaluate_rules(temp_fuzzy, cpu_fuzzy)
    ac_target = defuzzify_sugeno(rules)
    return ac_target
