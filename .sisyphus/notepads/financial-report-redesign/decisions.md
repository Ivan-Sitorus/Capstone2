## Decisions Made

### 1. Tab Implementation: Alpine.js + Blade (not Filament Tabs Schema)
**Decision**: Used Alpine.js-managed tabs in the Blade view instead of `Filament\Schemas\Components\Tabs` in the schema.
**Rationale**: 
- Schema Tabs are designed for form field groups, not page-level content with tables
- Blade approach gives full control over table rendering and action buttons
- Alpine.js is already bundled with Filament

### 2. Template Load: Direct Report Generation
**Decision**: Changed `loadTemplate` to directly generate a report instead of filling a form.
**Rationale**: Old form was removed from main page. Direct generation provides better UX - "Load and generate" in one click vs. "Load config, then click generate separately."

### 3. Download Routes: Closures in routes()
**Decision**: Used inline closures for download routes instead of controller methods.
**Rationale**: Plan says not to modify existing controllers. Filament's `ClassName@method` doesn't work with Livewire components. Closures are clean and self-contained.

### 4. Save as Template: Moved to ViewReport
**Decision**: "Save as Template" action is now on the ViewReport page instead of the main FinancialReport page.
**Rationale**: Users naturally want to save a report config after viewing/generating it, not before. Reduces friction.
