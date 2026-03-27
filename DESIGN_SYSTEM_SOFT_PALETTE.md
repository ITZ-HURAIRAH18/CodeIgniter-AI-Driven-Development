# Professional Soft Pastel ERP UI Design System

## Overview

This document outlines the complete design system for the modern, professional ERP/Inventory Management system with a soft, low-strain color palette designed for extended computer usage without eye fatigue.

---

## 1. Color Palette

### Surface Colors (Low-Strain Foundation)
- **Light Background**: `#fafbfc` - Main page background
- **Base Surface**: `#f8f9fa` - Card backgrounds and elevated content
- **Elevated Surface**: `#ffffff` - Modal windows and top-level elements

### Soft Pastel Accent Palette

#### Soft Pink (Primary)
- **50**: `#fdf6f8` - Hover states, highlights
- **100**: `#fce7f0` - Badges, light backgrounds
- **500**: `#e75ab8` - Primary interactive elements, buttons
- **600**: `#d63fa9` - Hover/active states

#### Soft Teal
- **50**: `#f0faf9`
- **500**: `#31a8a2` - Alternative accent, data visualization
- **Uses**: Secondary actions, teal-themed cards

#### Soft Purple
- **50**: `#f7f3fc`
- **500**: `#9059ae` - Dashboard stats, KPI indicators
- **Uses**: Analytics, special alerts

#### Soft Blue
- **50**: `#f0f6fb`
- **500**: `#3b9bc9` - Information, secondary actions
- **Uses**: Info badges, supporting elements

### Professional Gray Palette
- **50**: `#fafbfc` - Subtle backgrounds
- **100**: `#f3f4f6` - Hover states, headers
- **150**: `#e9ecf1` - Light borders
- **200**: `#e1e5eb` - Standard borders
- **300**: `#d1d5db` - Dividers
- **600**: `#4b5563` - Body text
- **900**: `#111827` - Headings, dark text

### Status Colors
- **Success**: `#10b981` (Green) - Completed, Optimal status
- **Warning**: `#f59e0b` (Amber) - Pending, Alert status
- **Error**: `#ef4444` (Red) - Low Stock, Critical
- **Info**: `#3b82f6` (Blue) - Information, Confirmed

---

## 2. Typography

### Font Family
Primary: `Inter`, `Poppins`, system fonts
Clean, modern sans-serif for professional appearance

### Font Scale
- **xs**: 0.75rem (10px) — Small labels, status badges
- **sm**: 0.875rem (14px) — Form labels, body text
- **base**: 1rem (16px) — Standard body, buttons
- **lg**: 1.125rem (18px) — Subheadings
- **xl**: 1.25rem (20px) — Section titles
- **2xl**: 1.5rem (24px) — Page titles
- **3xl**: 1.875rem (30px) — Main headers

### Font Weights
- **300**: Light (rarely used)
- **400**: Normal (body text)
- **500**: Medium (labels, strong text)
- **700**: Bold (headings, emphasis)

### Text Hierarchy
1. **Headings (H1-H3)**: `font-size-2xl/3xl`, `font-weight-bold`, `color-gray-900`
2. **Labels**: `font-size-xs/sm`, `font-weight-semibold`, `uppercase`, `tracking-wide`
3. **Body**: `font-size-sm/base`, `font-weight-normal`, `color-gray-600`
4. **Helper Text**: `font-size-xs`, `color-gray-500`

---

## 3. Spacing System

Consistent spacing ensures visual rhythm and proper alignment:

- **xs**: 0.25rem (4px)
- **sm**: 0.5rem (8px)
- **md**: 1rem (16px)
- **lg**: 1.5rem (24px)
- **xl**: 2rem (32px)
- **2xl**: 2.5rem (40px)
- **3xl**: 3rem (48px)

### Usage Guidelines
- **Padding**: Cards use `p-6`, inputs use `px-4 py-2.5`
- **Margins**: Section spacing uses `space-y-6`, item spacing uses `gap-4`
- **Grid**: KPI cards in `grid gap-4`, content in `grid gap-6`

---

## 4. Elevation & Shadows

Subtle shadows create depth without harshness:

- **shadow-xs**: `0 1px 2px 0 rgba(0, 0, 0, 0.05)` — Minimal lift
- **shadow-sm**: `0 1px 3px 0 rgba(0, 0, 0, 0.1)` — Standard cards
- **shadow-md**: `0 4px 6px -1px rgba(0, 0, 0, 0.1)` — Elevated cards, dropdowns
- **shadow-lg**: `0 10px 15px -3px rgba(0, 0, 0, 0.1)` — Modals, heavy elevation

**Best Practices**:
- Cards: `shadow-xs`, hover effects add `shadow-sm`
- Modals: `shadow-lg`
- Inputs: `shadow-xs` on focus
- Buttons: No shadow (background color provides affordance)

---

## 5. Border Radius

Consistent rounded corners for modern feel:

- **sm**: 0.25rem (4px) — Minimal rounding
- **base**: 0.375rem (6px) — Small UI elements
- **md**: 0.5rem (8px) — Standard buttons, inputs, cards
- **lg**: 0.75rem (12px) — Large cards, modals, prominent elements
- **xl**: 1rem (16px) — Extra large containers

---

## 6. Component Specifications

### Cards
- **Background**: `bg-surface-elevated` (white)
- **Border**: `border border-gray-200`
- **Shadow**: `shadow-xs`, hover: `shadow-md`
- **Rounded**: `rounded-lg` (0.75rem)
- **Padding**: `p-6` (24px)
- **Header**: `bg-surface-base`, `border-b border-gray-100`

### Buttons

#### Primary (Soft Pink)
```
bg-accent-pink-500 text-white
hover:bg-accent-pink-600
focus:ring-2 focus:ring-accent-pink-500 focus:ring-offset-2
```

#### Secondary (White with Border)
```
bg-white border border-gray-200 text-gray-900
hover:bg-gray-50
focus:ring-2 focus:ring-accent-pink-500
```

#### Ghost (Transparent)
```
bg-transparent text-gray-600
hover:bg-gray-100
focus:ring-2 focus:ring-gray-300
```

#### Sizing
- **sm**: `h-8 px-3 py-1.5` (Small actions)
- **md**: `h-10 px-4 py-2.5` (Standard)
- **lg**: `h-12 px-6 py-3` (Large, prominent)

### Inputs & Selects
- **Border**: `border border-gray-200`
- **Padding**: `px-4 py-2.5` (40px height)
- **Focus**: `ring-1 ring-accent-pink-500` (not ring-2)
- **Rounded**: `rounded-md` (0.5rem)
- **Placeholder**: `text-gray-500`
- **Label**: `text-xs font-semibold text-gray-700 uppercase tracking-wide`

### Tables
- **Header**: `bg-surface-base`, `border-b border-gray-100`
- **Rows**: Alternate `bg-white` and `bg-surface-base`
- **Row Hover**: `hover:bg-accent-pink-50`
- **Cell Padding**: `px-6 py-4`
- **Text**: `text-sm text-gray-700`

### Status Badges
- **Success**: `bg-status-success/15 text-status-success`
- **Warning**: `bg-status-warning/15 text-status-warning`
- **Error**: `bg-status-error/15 text-status-error`
- **Neutral**: `bg-gray-100 text-gray-700`
- **Pink**: `bg-accent-pink-100 text-accent-pink-700`
- **Padding**: `px-3 py-1.5`
- **Rounded**: `rounded-full`

### Modals
- **Background Overlay**: `bg-black/50`
- **Modal**: `bg-surface-elevated`, `rounded-xl`, `shadow-lg`, `border border-gray-200`
- **Header**: `px-6 py-4`, `border-b border-gray-100`, `bg-surface-base`
- **Content**: `p-6`

---

## 7. Interactions & Transitions

### Transition Timing
- **Fast**: 150ms (standard interactions) — Hover states, focus rings
- **Base**: 200ms (content transitions) — Page routes, modal opens
- **Slow**: 300ms (complex animations) — Multi-step transitions

### Focus States
All interactive elements include:
- **Keyboard Focus**: `focus:ring-2` with appropriate color
- **Ring Color**: Pink for primary, Gray for secondary/ghost
- **Ring Offset**: `focus:ring-offset-2` for visibility

### Hover Effects
- **Buttons**: Background color change or tint
- **Cards**: Subtle shadow increase (`shadow-xs` → `shadow-md`)
- **Rows**: Background color change to accent-pink-50
- **Links**: Color deepening (pink-500 → pink-600)

### Loading States
- **Spinner**: `w-4 h-4 border-2 border-accent-pink-500 border-t-transparent rounded-full animate-spin`
- **Opacity**: `opacity-70` on disabled/loading buttons
- **Cursor**: `cursor-not-allowed` for disabled

---

## 8. Layout Structure

### Main Page Layout
```
|- Sidebar (w-60, fixed left-0)
   |- Logo section (h-16)
   |- Navigation (flex-1 overflow-y-auto)
   |- Bottom section (Settings, Logout)
   |- User profile card
|
|- Topbar (fixed top-0 left-60 h-16)
   |- Search input
   |- Notifications
   |- User menu
|
|- Main Content (pl-60 mt-16 p-6)
   |- Page content with bg-surface-light
```

### Content Sections
- **Page Header**: `space-y-6`
- **KPI Cards**: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4`
- **Filters**: `p-5` with `space-y-4`
- **Content Grid**: `grid grid-cols-1 lg:grid-cols-3 gap-6`

---

## 9. Accessibility

### Color Contrast
All text meets WCAG AA standards:
- **Headings**: Gray-900 on light backgrounds (21:1 ratio)
- **Body**: Gray-600 on light backgrounds (8:1 ratio)
- **Helper**: Gray-500 on light backgrounds (4.5:1 ratio)

### Keyboard Navigation
- Tab order follows visual flow
- All buttons and inputs keyboard accessible
- Focus rings clearly visible with 2px ring
- No keyboard traps

### Color Independence
- Status not indicated by color alone
- Text badges accompany colored badges
- Icons supplement color meanings

---

## 10. Implementation Files

### Updated Components
1. **Select.vue** - Custom select with new palette
2. **BaseButton.vue** - Five button variants
3. **BaseInput.vue** - Form input with proper focus states
4. **BaseCard.vue** - Card wrapper with optional header/footer
5. **BaseTable.vue** - Professional data table
6. **Badge.vue** - Status and category badges

### Layout Components
1. **SidebarPro.vue** - Professional sidebar navigation
2. **TopbarPro.vue** - Header with search and user menu
3. **AppLayoutPro.vue** - Master layout wrapper

### View Components
1. **DashboardView.vue** - KPI dashboard with charts
2. **InventoryViewPro.vue** - Advanced inventory table

### Configuration Files
1. **tailwind.config.js** - Extended theme with soft palette
2. **global.css** - Design tokens and base styles

---

## 11. Design Principles

1. **Low-Strain**: Soft colors reduce eye fatigue for extended use
2. **Professional**: Classic typography, refined spacing
3. **Clear Hierarchy**: Obvious visual distinction between elements
4. **Accessible**: High contrast, keyboard navigation, color independence
5. **Consistent**: Unified spacing, colors, and interactions across all pages
6. **Modern**: Subtle shadows, smooth transitions, rounded corners
7. **Functional**: Every design choice serves user needs

---

## 12. Future Enhancements

- Dark mode variant with adjusted palette
- Custom theme selection UI
- Animation library for complex interactions
- Component library documentation site
- Design tokens CSS exports

