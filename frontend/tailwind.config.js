/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // Soft pastel palette for low-strain, modern look
        'surface': {
          'light': '#fafbfc',    // Main page background
          'base': '#f8f9fa',     // Card backgrounds
          'elevated': '#ffffff', // Modal/overlay backgrounds
        },
        // Soft pastel accent colors
        'accent': {
          'pink': {
            50: '#fdf6f8',
            100: '#fce7f0',
            200: '#f8c5e0',
            300: '#f5a8d8',
            400: '#ef7fcc',
            500: '#e75ab8',   // Primary soft pink
            600: '#d63fa9',
            700: '#b8278f',
          },
          'teal': {
            50: '#f0faf9',
            100: '#d6f5f1',
            200: '#aae8e0',
            300: '#7dd9cc',
            400: '#5bc9bc',
            500: '#31a8a2',   // Soft teal
            600: '#2a9a96',
            700: '#1f7a78',
          },
          'purple': {
            50: '#f7f3fc',
            100: '#ede5f9',
            200: '#d9c8f0',
            300: '#c5abb0',
            400: '#a882d4',
            500: '#9059ae',   // Soft purple
            600: '#7e4b9f',
            700: '#62387e',
          },
          'blue': {
            50: '#f0f6fb',
            100: '#d6ebf5',
            200: '#a8d8eb',
            300: '#7ac4e1',
            400: '#5ab5d9',
            500: '#3b9bc9',   // Soft blue
            600: '#2d83b3',
            700: '#1f6196',
          },
        },
        // Semantic grays (professional, neutral)
        'gray': {
          50: '#fafbfc',
          100: '#f3f4f6',
          150: '#e9ecf1',
          200: '#e1e5eb',
          300: '#d1d5db',
          400: '#9ca3af',
          500: '#6b7280',
          600: '#4b5563',   // Body text
          700: '#374151',
          800: '#1f2937',
          900: '#111827',   // Headings
        },
        // Status colors (rounded, softer)
        'status': {
          'success': '#10b981',    // Green
          'warning': '#f59e0b',    // Amber
          'error': '#ef4444',      // Red
          'info': '#3b82f6',       // Blue
        },
      },
      fontFamily: {
        sans: ['Inter', 'Poppins', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
      },
      fontSize: {
        // Professional typography scale
        'xs': ['0.75rem', { lineHeight: '1rem', letterSpacing: '0.5px' }],
        'sm': ['0.875rem', { lineHeight: '1.25rem' }],
        'base': ['1rem', { lineHeight: '1.5rem' }],
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],
        'xl': ['1.25rem', { lineHeight: '1.75rem', letterSpacing: '-0.5px' }],
        '2xl': ['1.5rem', { lineHeight: '2rem', letterSpacing: '-0.5px' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem', letterSpacing: '-0.5px' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem', letterSpacing: '-0.5px' }],
      },
      fontWeight: {
        light: '300',
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
      },
      spacing: {
        // Consistent spacing system
        'xs': '0.25rem',
        'sm': '0.5rem',
        'md': '1rem',
        'lg': '1.5rem',
        'xl': '2rem',
        '2xl': '2.5rem',
        '3xl': '3rem',
      },
      boxShadow: {
        // Elevation system
        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        'sm': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
      },
      borderRadius: {
        'sm': '0.25rem',
        'base': '0.375rem',
        'md': '0.5rem',
        'lg': '0.75rem',
      },
      keyframes: {
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        'slide-in': {
          '0%': { opacity: '0', transform: 'translateY(4px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
      },
      animation: {
        'fade-in': 'fade-in 200ms ease-out',
        'slide-in': 'slide-in 200ms ease-out',
      },
    },
  },
  plugins: [],
}
