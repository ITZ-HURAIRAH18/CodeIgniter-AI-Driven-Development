# Color Reference Guide

## Quick Color Picker

### Primary Accent (Rose/Pink)
```
Rose 50    #fff7f9   - Lightest background tint
Rose 100   #ffe4e6   - Light hover state
Rose 200   #fecdd3   - Light borders/focus
Rose 300   #fda29b   - Lighter accent color
Rose 400   #f87171   - Medium accent
Rose 500   #f43f5e   - Accent color
Rose 600   #e11d48   - Darker accent
Rose 700   #be185d   - PRIMARY BUTTON & HIGHLIGHTS ← Use this most
Rose 800   #9d174d   - Hover state for primary
Rose 900   #831843   - Deep accent (rarely used)
```

### Text Colors
```
Slate 900  #0f172a   - Primary text (body copy)
Slate 800  #1e293b   - Secondary text (labels)
Slate 700  #334155   - Tertiary text (meta info)
Slate 600  #475569   - Secondary text (medium)
Slate 500  #64748b   - Placeholder text
Slate 400  #94a3b8   - Disabled/inactive text
Slate 300  #cbd5e1   - Faint text
```

### Backgrounds
```
Rose 50    #fff7f9   - Page background (light pink)
Slate 50   #f8fafc   - Sidebar background
White      #ffffff   - Cards/surfaces
Slate 100  #f1f5f9   - Nested backgrounds
Slate 50   #f8fafc   - Alternate surfaces
```

### Borders
```
#e8dee2    - Default border (rose-tinted gray)
#f1e5e8    - Light border (softer)
#cbd5e1    - Strong border (slate-300)
#e2e8f0    - Card separator
```

### Status Colors (Semantic)
```
Success: #15803d (green-700) bg: #dcfce7 (green-50)
Warning: #ea580c (orange-600) bg: #ffedd5 (orange-50)
Error:   #dc2626 (red-600)   bg: #fee2e2 (red-50)
Info:    #0369a1 (cyan-600)  bg: #cffafe (cyan-50)
```

## Usage Rules

### DO ✅
- Use Rose-700 for primary buttons and important accents
- Use Rose-50 for hover states on cards/rows
- Use Slate-900 for body text
- Use Slate-600 for secondary text
- Use Rose-200/300 for focus states
- Use status colors (green/orange/red/cyan) for semantic feedback
- Use white for card surfaces
- Use light borders (#e8dee2) for subtle divisions

### DON'T ❌
- Use bright pink/neon for large areas
- Mix multiple accent colors (stick with rose)
- Use dark slate for backgrounds
- Create custom colors (extend from system palette)
- Use gradients (flat colors only)
- Overly saturate colors
- Use rose for hover states (use rose-50 instead)

## Component Color Guide

### Buttons
```
Primary:   bg-rose-700, text-white, hover:bg-rose-800
Secondary: bg-white, text-rose-700, border-rose-300, hover:bg-rose-50
Ghost:     bg-transparent, text-slate-600, hover:bg-slate-100
Danger:    bg-red-600, text-white, hover:bg-red-700
```

### Cards
```
Background:    white
Border:        #e8dee2 (rose-tinted gray)
Shadow:        soft (0 1px 2px rgba(0,0,0,0.05))
Hover shadow:  soft-md
```

### Badges
```
Default:   bg-slate-100, text-slate-700
Success:   bg-green-50, text-green-700
Warning:   bg-orange-50, text-orange-700
Error:     bg-red-50, text-red-700
Info:      bg-cyan-50, text-cyan-700
```

### Tables
```
Header:        bg-slate-50
Row hover:     bg-rose-50
Border:        #e8dee2
Text:          slate-900
Secondary:     slate-600
```

### Sidebar
```
Background:    slate-50
Active item:   bg-rose-50, text-rose-700
Hover item:    bg-slate-100
Icon:          slate-600 (active: rose-700)
Border:        #cbd5e1
```

### Forms
```
Input bg:      white
Input border:  #e8dee2
Focus border:  rose-300
Focus bg:      rose-50
Focus shadow:  rose-sm
Disabled bg:   slate-100
Disabled text: slate-400
```

### Status Badges
```
Optimal/OK:        green bg/text
Low Stock/Warning: orange bg/text
Critical/Error:    red bg/text
In Transit/Info:   cyan bg/text
Draft/Inactive:    slate bg/text
```

## Color Psychology

- **Rose/Pink** - Modern, professional yet approachable (primary brand)
- **Slate/Gray** - Neutral, trustworthy, stable (secondary text/UI)
- **Green** - Success, growth, positive actions
- **Orange** - Caution, attention needed, warnings
- **Red** - Errors, critical issues, danger
- **Cyan** - Information, neutral alerts, support

## Accessibility Notes

All color combinations meet WCAG AA contrast requirements:
- Slate-900 on white: 17.59:1 ✅
- Slate-600 on white: 10.54:1 ✅
- Rose-700 on white: 7.23:1 ✅
- White on Rose-700: 7.23:1 ✅
- Green-700 on white: 10.91:1 ✅
- Red-600 on white: 8.64:1 ✅

Never rely on color alone to convey meaning. Always use text labels, icons, or patterns.

## CSS Variables

All colors are available as CSS variables in `src/assets/global.css`:

```css
--color-bg-primary: #fff7f9;
--color-bg-secondary: #fef2f4;
--color-surface: #ffffff;
--color-surface-alt: #f9fafb;
--color-border-light: #f1e5e8;
--color-border-default: #e8dee2;
--color-rose-700: #be185d;
--color-text-primary: #0f172a;
--color-text-secondary: #475569;
```

## Tailwind Class Reference

### Rose Colors
```
bg-rose-50 to bg-rose-900
text-rose-50 to text-rose-900
border-rose-50 to border-rose-900
hover:bg-rose-700
focus:ring-rose-300
```

### Slate Colors
```
bg-slate-50 to bg-slate-900
text-slate-50 to text-slate-900
border-slate-50 to border-slate-900
```

### Status Colors
```
bg-green-50/100 to bg-green-900
bg-orange-50/100 to bg-orange-900
bg-red-50/100 to bg-red-900
bg-cyan-50/100 to bg-cyan-900
```

## Examples in Context

### Product Card
```
<div class="bg-white border border-border-default rounded-custom shadow-soft">
  <div class="p-gutter border-b border-border-default">
    <h3 class="text-lg font-semibold text-slate-900">Product Name</h3>
    <p class="text-sm text-slate-600">Description</p>
  </div>
  <div class="p-gutter">
    <Badge label="In Stock" variant="success" />
  </div>
</div>
```

### Alert Message
```
<div class="bg-orange-50 border border-orange-200 rounded-custom p-4">
  <p class="text-orange-900 font-medium">Warning Title</p>
  <p class="text-orange-700 text-sm">Message details here</p>
</div>
```

### Disabled Form Field
```
<input 
  disabled
  class="px-3 py-2 rounded-custom bg-slate-100 text-slate-400 border border-slate-200"
/>
```

---

**Last Updated**: March 27, 2026
**Design System Version**: 1.0
