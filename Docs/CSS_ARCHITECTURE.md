# CSS Architecture

## Folder Structure

```
css/
├── core/                    # Foundation styles
│   ├── variables.css        # Design tokens (colors, fonts, spacing)
│   ├── base.css            # CSS reset, typography, utilities
│   └── compatibility.css    # Browser polyfills & fixes
│
├── components/              # Reusable UI components
│   ├── shared.css          # Cards, alerts, badges, modals, tooltips
│   ├── buttons.css         # Button styles & variants
│   ├── forms.css           # Form elements, inputs, selects
│   ├── tables.css          # Table styles & responsive tables
│   ├── search.css          # Search bar & autocomplete
│   └── calendar.css        # Calendar component
│
├── layout/                  # Page structure
│   ├── header.css          # Site header
│   ├── navbar.css          # Navigation bar
│   └── footer.css          # Site footer
│
├── pages/                   # Page-specific styles
│   ├── home.css            # Homepage
│   ├── auth.css            # Login & register pages
│   ├── clubs.css           # Club pages
│   ├── events.css          # Event pages
│   ├── profiles.css        # User profiles
│   ├── dashboard.css       # User dashboard
│   ├── admin.css           # Admin panel
│   ├── validation.css      # Validation pages
│   └── errors.css          # Error pages (403, 404, 500)
│
├── responsive.css          # Media queries (loads last)
└── main.css               # Import all CSS (optional single file)
```

## Loading Strategy (Optimized)

Only essential CSS is loaded automatically. Everything else is loaded per-page via `$pageCss`.

### Auto-loaded (on every page):

- `core/variables.css` - Design tokens
- `core/base.css` - Reset & typography
- `layout/header.css` - Header styles
- `layout/navbar.css` - Navigation
- `layout/footer.css` - Footer styles
- `responsive.css` - Media queries (last)

### Loaded via $pageCss:

Everything else must be explicitly declared:

- Components: `shared`, `buttons`, `forms`, `tables`, `search`, `calendar`
- Pages: `home`, `auth`, `clubs`, `events`, `profiles`, `dashboard`, `admin`, `validation`

## Usage in Views

Each view specifies exactly which CSS files it needs:

```php
<?php
// At the top of your view file, before including head.php
// Order: components first, then page-specific
$pageCss = ['shared', 'buttons', 'forms', 'clubs'];

// Then include head.php
include __DIR__ . '/../includes/head.php';
```

### Available CSS Names for $pageCss

**Components (from css/components/):**

- `shared` - Cards, alerts, badges, modals ⭐ Most pages need this
- `buttons` - Button styles ⭐ Most pages need this
- `forms` - Form elements, inputs, selects
- `tables` - Data tables
- `search` - Search bar & filters
- `calendar` - Calendar component

**Pages (from css/pages/):**

- `home` - Homepage styles
- `auth` - Authentication pages (login/register)
- `clubs` - Club-related pages
- `events` - Event-related pages
- `profiles` - User profile pages
- `dashboard` - Dashboard pages
- `admin` - Admin panel
- `validation` - Validation pages
- `errors` - Error pages

### Examples by Page Type

```php
// Login/Register page
$pageCss = ['shared', 'buttons', 'forms', 'auth'];

// Club list with search
$pageCss = ['shared', 'buttons', 'forms', 'search', 'tables', 'clubs'];

// Admin dashboard
$pageCss = ['shared', 'buttons', 'tables', 'admin', 'dashboard'];

// Simple profile view
$pageCss = ['shared', 'buttons', 'profiles'];
```

## CSS Variables

All design tokens are defined in `core/variables.css`:

```css
:root {
  /* Colors */
  --color-primary: #007bff;
  --color-secondary: #6c757d;

  /* Typography */
  --font-primary: "Segoe UI", system-ui, sans-serif;
  --font-size-base: 1rem;

  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;

  /* And more... */
}
```

## Best Practices

1. **Use CSS Variables** - Always use `var(--variable-name)` for consistency
2. **Mobile First** - Write base styles for mobile, use media queries for larger screens
3. **Component-based** - Keep styles modular and reusable
4. **Avoid !important** - Use proper specificity instead
5. **Comment sections** - Use comment headers to organize CSS within files
