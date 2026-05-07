## FinancialReport Redesign — Key Learnings

### Filament v3.1.2 Architecture Notes
- Page class uses `InteractsWithHeaderActions` trait for header action buttons
- Actions can have `modal()` + `schema()` for modal forms
- Custom pages with route parameters override `routes()` to register additional routes
- Must match parent `routes(Panel, ?PageConfiguration)` signature exactly
- SPA mode requires `spaUrlExceptions` for non-Livewire routes (downloads)
- `getUrl()` passes array to `route()`, so parameters must match route definition

### Schema vs Forms Components
- `Filament\Schemas\Components` (from filament/schemas) is the new API
- `Filament\Forms\Components` (from filament/forms) still works for backward compat
- Both can be used in Action `schema()` and Page `form()`

### Route Patterns for Custom Pages
- `$slug = 'view-report/{id}'` creates `/admin/view-report/{id}`
- Override `getRelativeRouteName()` to control route name
- Additional routes (downloads) registered via `routes()` using closures
- Cannot use `ClassName@method` syntax with Livewire components

### Template Load Redesign
- Old `loadTemplate` filled a form that no longer exists on main page
- Changed to directly generate report from template config → cleaner UX
- Template save moved to ViewReport header action → better workflow
