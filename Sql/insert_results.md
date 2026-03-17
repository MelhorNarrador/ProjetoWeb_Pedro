**Estas respostas foram obtidas atravez de GET no postman com os dados simulados do ficheiro Inserts_teste_algoritmo.sql

resposta 1)

{
    "success": true,
    "data": {
        "current_moisture": 51,
        "min_moisture": 40,
        "average_moisture": 63.2,
        "dry_in_hours": 1.21,
        "dry_in_human": "1h 12m",
        "dry_at": "2026-03-18 00:02:38",
        "trend_per_hour": 9.12,
        "r_squared": 0.979,
        "confidence": "high",
        "data_points_used": 10,
        "span_hours": 2.25,
        "status": "drying"
    }
}

---

resposta 2)

{
    "success": true,
    "prediction": "recently_watered",
    "spike_detected": 36,
    "cooldown_minutes": 30
}

---

resposta 3)

{
    "success": true,
    "prediction": "increasing_moisture",
    "r_squared": 0.997
}

---

resposta 4)

{
    "success": true,
    "prediction": "insufficient_data",
    "message": "Mínimo de 3 leituras necessárias"
}

---

resposta 5)

{
    "success": true,
    "prediction": "unstable_data",
    "message": "Tendência inconsistente (R² baixo)",
    "r_squared": 0.194
}

---

resposta 6)

{
    "success": true,
    "prediction": "unstable_data",
    "message": "Tendência inconsistente (R² baixo)",
    "r_squared": 0
}

---

resposta 7)

{
    "success": true,
    "data": {
        "current_moisture": 52,
        "min_moisture": 40,
        "average_moisture": 55,
        "dry_in_hours": 0.67,
        "dry_in_human": "40m",
        "dry_at": "2026-03-17 23:33:31",
        "trend_per_hour": 18,
        "r_squared": 1,
        "confidence": "low",
        "data_points_used": 3,
        "span_hours": 0.33,
        "status": "drying"
    }
}

---

resposta 8)

{
    "success": true,
    "data": {
        "current_moisture": 42,
        "min_moisture": 40,
        "average_moisture": 60.5,
        "dry_in_hours": 0.04,
        "dry_in_human": "2m",
        "dry_at": "2026-03-17 22:56:30",
        "trend_per_hour": 45.77,
        "r_squared": 0.999,
        "confidence": "medium",
        "data_points_used": 6,
        "span_hours": 0.83,
        "status": "drying"
    }
}

---

resposta 9)

{
    "success": true,
    "data": {
        "current_moisture": 60,
        "min_moisture": 40,
        "average_moisture": 62.5,
        "dry_in_hours": 6.67,
        "dry_in_human": "6h 40m",
        "dry_at": "2026-03-18 05:34:18",
        "trend_per_hour": 3,
        "r_squared": 1,
        "confidence": "medium",
        "data_points_used": 6,
        "span_hours": 1.67,
        "status": "drying"
    }
}

---

resposta 10)

{
    "success": true,
    "data": {
        "current_moisture": 52,
        "min_moisture": 40,
        "average_moisture": 56.8,
        "dry_in_hours": 1.81,
        "dry_in_human": "1h 48m",
        "dry_at": "2026-03-18 00:43:15",
        "trend_per_hour": 6.63,
        "r_squared": 0.998,
        "confidence": "medium",
        "data_points_used": 5,
        "span_hours": 1.5,
        "status": "drying"
    }
}
