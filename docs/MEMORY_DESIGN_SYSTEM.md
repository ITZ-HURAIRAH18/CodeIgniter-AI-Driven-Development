# 🎨 Design System - Soft Pastel Professional ERP UI

## Summary
Professional ERP UI redesign with soft, low-strain color palette designed for reduced eye fatigue.

## Core Colors
- **Primary Accent**: Soft pink `#be185d` (rose-700)
- **Alternative Primary**: `#ec4899` (accent-pink-500) - for newer components
- **Surface Light**: `#fafbfc`, **Base**: `#f8f9fa`, **Elevated**: `#ffffff`
- **Status**: success `#10b981`, warning `#f59e0b`, error `#ef4444`, info `#3b82f6`
- **Accents**: Teal `#31a8a2`, Purple `#9059ae`, Blue `#3b9bc9`
- **Grays**: Professional 50-900 semantic palette (not rose/slate)

## Key Design Rules

### 1. Focus Rings
- Use `ring-1` (subtle) not `ring-2`
- Color: `ring-accent-pink-500` or `ring-rose-700`

### 2. Borders
- **Primary**: Gray-100/200 only (soft, not harsh)
- Use `border border-gray-200` for cards
- **Do NOT use**: harsh borders or border-2 with dark colors

### 3. Card Backgrounds
- **Base**: `bg-white`
- **Elevated**: `bg-white` with `border border-gray-200`
- **Hover**: Increase `shadow-md` or use `bg-rose-50` (very light)

### 4. Hover States
- Cards: `hover:shadow-md`
- Links: `hover:bg-rose-50` (light background)
- Buttons: Add slight shadow increase

### 5. Buttons
- **Primary**: `bg-rose-700` or `bg-accent-pink-500` text white
- **Secondary**: `border border-gray-200` bg-white text-slate-900
- **Ghost**: No background, text-rose-700

### 6. Tables
- Zebra striping: white/`bg-gray-50`
- Hover: `hover:bg-rose-50`
- Headers: `bg-gray-50` with `border-gray-200`

### 7. Shadows
- Subtle progression: xs < sm < md < lg < xl
- Never harsh shadows
- Use `shadow-sm` for cards, `shadow-md` on hover

### 8. Typography
- **Font**: Inter (base) + Poppins (headings)
- **Scale**: xs (10px), sm (12px), base (14px), lg (16px), xl (18px), 2xl (20px), 3xl (24px), 4xl (30px)
- Professional and readable

## Updated Components

### Completed
- ✅ BaseButton (primary, secondary, ghost, danger styles)
- ✅ BaseInput (text, focus states, soft borders)
- ✅ BaseCard (title, subtitle, footer, borders)
- ✅ BaseTable (compact ERP style)
- ✅ Badge (status colors)
- ✅ Select (dropdown with soft styling)
- ✅ SidebarPro (colors, active states)
- ✅ Dashboard (KPI redesign with colored top borders 3px)
- ✅ Inventory (status colors, Badge integration)

### Pending
- Orders view
- Products view
- Branches view
- Transfer view

## Tailwind Config Extensions

```javascript
theme.colors = {
  surface: {
    light: '#fafbfc',
    base: '#f8f9fa',
    elevated: '#ffffff'
  },
  accent: {
    pink: { 50, 500, 600, 700 },
    teal: { 50, 500, 600, 700 },
    purple: { 50, 500, 600, 700 },
    blue: { 50, 500, 600, 700 }
  },
  status: {
    success: '#10b981',
    warning: '#f59e0b',
    error: '#ef4444',
    info: '#3b82f6'
  }
}
```

## CSS Design Tokens (in global.css)

```css
:root {
  --color-primary: #be185d;
  --color-primary-light: #ec4899;
  --color-bg-surface-light: #fafbfc;
  --color-bg-surface-base: #f8f9fa;
  --color-bg-surface-elevated: #ffffff;
  --color-status-success: #10b981;
  --color-status-warning: #f59e0b;
  --color-status-error: #ef4444;
  --color-status-info: #3b82f6;
  --transition-fast: 100ms;
  --transition-base: 150ms;
  --transition-slow: 300ms;
}
```

## Implementation Pattern

When updating components:
1. Replace `brand-500` → `accent-pink-500` or `rose-700`
2. Replace `rose-*` → `gray-*` (for soft palette)
3. Use `border-gray-200` instead of harsh borders
4. Add `shadow-sm` to cards, `shadow-md` on hover
5. Use `bg-rose-50` for light interactive states

## Status

- Dashboard & Inventory redesigned with soft palette
- Products, Orders, Branches pending same approach
- Color consistency needs standardization (rose-700 vs accent-pink-500)

## Best Practices

1. **No gradients** - Keep backgrounds solid
2. **No neon colors** - Use soft palette only
3. **No flashy animations** - Keep transitions smooth (150ms)
4. **Generous whitespace** - Professional feel
5. **Semantic colors** - Green=success, Orange=warning, Red=error, Blue=info
6. **Accessibility** - WCAG AA contrast ratios
7. **Responsive** - Mobile-first approach
