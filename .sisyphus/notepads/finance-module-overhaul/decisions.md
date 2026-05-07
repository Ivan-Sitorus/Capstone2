# Decisions — finance-module-overhaul

| Date | Decision | Rationale |
|------|----------|-----------|
| 2026-05-06 | FK order_id nullable + nullOnDelete | Receivable stays even if order deleted |
| 2026-05-06 | Template system = filter presets only | Avoid scope creep into report builder |
| 2026-05-06 | Cash basis accounting | is_paid = recognized |
| 2026-05-06 | No polling on CashFlow | Refresh on period change only |
