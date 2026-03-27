# ERP Design System - Soft Pink Accent Theme

## Philosophy
A professional, production-ready SaaS ERP interface with a soft pink accent theme. Clean, elegant, enterprise-grade. Every element should feel intentional and refined.

## Color Palette

### Background & Surfaces
- **Page Background**: `#fff7f9` (rose-50) - Very light soft pink
- **Secondary Background**: `#fef2f4` (rose-100) - Slightly more pink for contrast
- **Surface/Card**: `#ffffff` - Pure white for content
- **Surface Alt**: `#f9fafb` - Subtle gray for nested elements
- **Sidebar Background**: `#f8fafc` with subtle rose tint

### Text & Foreground
- **Primary Text**: `#0f172a` (slate-900)
- **Secondary Text**: `#475569` (slate-600)
- **Tertiary Text**: `#94a3b8` (slate-400)
- **Disabled Text**: `#cbd5e1` (slate-300)

### Accents & Interactive
- **Primary Accent**: `#be185d` (rose-700) - Muted rose for buttons
- **Accent Hover**: `#9d174d` (rose-800) - Deeper for interaction
- **Accent Light**: `#fda29b` (rose-300) - For backgrounds
- **Accent Lighter**: `#fecdd3` (rose-200) - For borders/focus
- **Accent Lightest**: `#ffe4e6` (rose-100) - For hover states

### Borders
- **Light Border**: `#f1e5e8` (custom rose-tinted)
- **Default Border**: `#e8dee2` (custom rose-tinted)
- **Strong Border**: `#cbd5e1` (slate-300)

### Status Colors
- **Success**: `#15803d` (green-700)
- **Warning**: `#ea580c` (orange-600)
- **Error**: `#dc2626` (red-600)
- **Info**: `#0369a1` (cyan-600)

## Typography

### Font Stack
`Inter`, `ui-sans-serif`, `system-ui`, `-apple-system`, `sans-serif`

### Scale
- **Display**: 1.875rem / 2.25rem (headings)
- **Heading Large**: 1.5rem / 2rem (h2)
- **Heading**: 1.25rem / 1.75rem (h3)
- **Title**: 1.125rem / 1.75rem (card titles)
- **Body**: 1rem / 1.5rem (regular text)
- **Small**: 0.875rem / 1.25rem (labels, secondary)
- **Extra Small**: 0.75rem / 1rem (badges, hints)

### Weight
- **Regular**: 400
- **Medium**: 500
- **Semibold**: 600
- **Bold**: 700

### Line Height
- Always use generous line-height for readability
- Body: 1.5
- Headings: 1.2-1.3

## Spacing System

- **XS**: 0.25rem
- **SM**: 0.5rem
- **Base**: 1rem
- **MD**: 1.5rem
- **LG**: 2rem
- **XL**: 3rem
- **2XL**: 4rem
- **Gutter**: 1.25rem (card padding)
- **Section**: 2rem (between sections)

## Shadows

### Elevation System
- **Soft**: `0 1px 2px rgba(0,0,0,0.05)` - Subtle lift
- **Soft-MD**: `0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03)` - Moderate lift
- **Soft-LG**: `0 10px 15px -3px rgba(0,0,0,0.06), 0 4px 6px -2px rgba(0,0,0,0.02)` - Noticeable lift
- **Rose-SM**: `0 1px 2px rgba(244,63,94,0.05)` - Rose-tinted subtle

## Border Radius

- **None**: 0
- **SM**: 0.25rem
- **Base**: 0.375rem
- **Custom**: 0.5rem (default for components)
- **MD**: 0.5rem
- **LG**: 0.75rem
- **XL**: 1rem
- **Full**: 9999px (pills)

## Component Patterns

### Cards
- Background: white
- Border: 1px solid `#e8dee2` (rose-tinted gray)
- Radius: 8px
- Padding: 1.25rem
- Shadow: soft
- Hover: subtle shadow-md, no color change

### Buttons
- **Primary**: Rose-700 bg, white text, hover to Rose-800
- **Secondary**: Rose-700 text, transparent bg, Rose-50 hover
- **Ghost**: Slate-600 text, transparent bg, Slate-50 hover
- **Danger**: Red-600 bg, white text
- **Padding**: 0.75rem 1rem
- **Radius**: 0.375rem

### Inputs
- Background: white
- Border: 1px solid `#e8dee2`
- Focus: Rose-300 border, Rose-50 background, shadow-rose-sm
- Padding: 0.625rem 0.75rem
- Radius: 0.375rem

### Tables
- Header background: Slate-50
- Row hover: Rose-50
- Border: 1px solid `#e8dee2`
- Padding: 0.75rem 1rem
- Clean, compact ERP style

### Sidebar
- Background: Slate-50 with subtle rose tint
- Active item: Rose-50 background, Rose-700 text
- Border-right: 1px solid `#cbd5e1`
- Icons: Slate-600 (active: Rose-700)

### Badges/Pills
- Padding: 0.375rem 0.75rem
- Radius: full (9999px)
- Font size: 0.75rem
- Success: Green-50 bg, Green-700 text
- Warning: Orange-50 bg, Orange-700 text
- Error: Red-50 bg, Red-700 text
- Default: Slate-100 bg, Slate-700 text

## Transitions

- **Default**: 150ms ease-out
- **Hover**: 100ms ease-out
- **Expand**: 200ms ease-out

## Consistency Rules

1. **No gradients** - They look dated and marketing-like
2. **Soft pink only for accents** - Never fill large areas with bright pink
3. **Generous whitespace** - Professional products have breathing room
4. **Semantic color usage** - Don't surprise users with unexpected colors
5. **Accessibility first** - All text must have proper contrast
6. **Micro-interactions** - Smooth, subtle transitions on all interactive elements
7. **Consistent spacing** - Use the spacing system, not arbitrary values
8. **Professional fonts only** - Inter for everything
9. **Icon consistency** - All icons from lucide-icons, 20px or 24px, always consistent weight

## Anti-Patterns to Avoid

❌ Neon colors
❌ Bright saturation
❌ Asymmetrical padding
❌ Inconsistent shadows
❌ Too many font sizes
❌ Blurry or colored backgrounds
❌ Generic template look
❌ Flashy animations

