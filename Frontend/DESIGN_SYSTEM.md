# SAKTI Mini - Frontend Design System

## Overview
Frontend SAKTI Mini telah diperbarui dengan desain desktop-first yang modern, konsisten, dan responsif. Desain ini menggunakan pendekatan component-based dengan sistem warna dan tipografi yang terpadu.

## Design Principles

### 1. Desktop-First Approach
- Desain dioptimalkan untuk layar desktop (1200px+)
- Responsive breakpoints: 768px (tablet), 480px (mobile)
- Layout menggunakan CSS Grid dan Flexbox

### 2. Modern Visual Language
- Gradient backgrounds dan shadows untuk depth
- Rounded corners (12px-20px) untuk modern feel
- Consistent spacing system (0.25rem increments)
- Icon-driven interface dengan SVG icons

### 3. Accessibility & UX
- High contrast colors untuk readability
- Focus states dan keyboard navigation
- Loading states dan error handling
- Clear visual hierarchy

## Color Palette

### Primary Colors
- **Primary Gradient**: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`
- **Background**: `linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)`

### Semantic Colors
- **Success**: #16a34a (Green)
- **Error**: #ef4444 (Red)
- **Warning**: #d97706 (Orange)
- **Info**: #2563eb (Blue)

### Neutral Colors
- **Text Primary**: #1f2937
- **Text Secondary**: #6b7280
- **Text Muted**: #9ca3af
- **Border**: #e5e7eb
- **Background**: #f9fafb

## Typography

### Font Family
- **Primary**: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif

### Font Weights
- Light: 300
- Regular: 400
- Medium: 500
- Semibold: 600
- Bold: 700

### Font Sizes
- **Heading 1**: 2rem (32px)
- **Heading 2**: 1.75rem (28px)
- **Heading 3**: 1.5rem (24px)
- **Body Large**: 1.125rem (18px)
- **Body**: 1rem (16px)
- **Body Small**: 0.875rem (14px)
- **Caption**: 0.75rem (12px)

## Component Architecture

### 1. Layout Components
- **App.vue**: Main application shell dengan header, main, footer
- **Header**: Navigation dengan gradient background
- **Footer**: Simple footer dengan copyright

### 2. Page Components
- **LoginForm**: Modern login dengan icons dan animations
- **RegisterForm**: Consistent dengan LoginForm
- **Dashboard**: Card-based layout dengan stats dan quick actions
- **ProfilePage**: Detailed user information dengan avatar

### 3. UI Elements

#### Cards
```css
.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 2rem;
}
```

#### Buttons
```css
.button-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  font-weight: 600;
}
```

#### Form Inputs
```css
.form-input {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  transition: all 0.2s ease;
}

.form-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}
```

## Responsive Design

### Breakpoints
- **Desktop**: 1024px+
- **Tablet**: 768px - 1023px
- **Mobile**: 320px - 767px

### Grid System
- Desktop: 3-4 columns
- Tablet: 2 columns
- Mobile: 1 column

### Typography Scale
- Desktop: Full scale
- Tablet: 90% scale
- Mobile: 85% scale

## Animation & Interactions

### Hover Effects
- `transform: translateY(-1px)` untuk buttons dan cards
- `box-shadow` enhancement
- Color transitions

### Loading States
- Spinning SVG icons
- Skeleton loading untuk content
- Disabled states dengan opacity

### Transitions
- Duration: 0.2s ease
- Properties: transform, box-shadow, colors

## Icons

### Icon System
- SVG icons untuk scalability
- 16px, 20px, 24px, 32px, 48px, 64px sizes
- Consistent stroke-width: 2px
- Outline style untuk consistency

### Icon Categories
- **Navigation**: arrows, home, profile
- **Actions**: login, logout, refresh, edit
- **Status**: success, error, warning, info
- **UI**: close, menu, search, settings

## File Structure

```
src/
├── assets/
│   ├── styles/
│   │   └── utilities.css     # Utility classes
│   └── main.css              # Global styles
├── components/
│   ├── LoginForm.vue         # Login page
│   ├── RegisterForm.vue      # Registration page
│   ├── Dashboard.vue         # Dashboard page
│   └── ProfilePage.vue       # Profile page
└── App.vue                   # Main app shell
```

## Utility Classes

Tersedia utility classes untuk spacing, colors, typography, dan layout:

```css
/* Spacing */
.mt-4, .mb-4, .p-4, .gap-4

/* Colors */
.text-gray-600, .bg-white, .border-gray-200

/* Typography */
.text-lg, .font-semibold, .text-center

/* Layout */
.flex, .grid, .items-center, .justify-between
```

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Performance Considerations

- CSS-in-Vue untuk component isolation
- Minimal external dependencies
- Optimized SVG icons
- Efficient CSS selectors
- Lazy loading untuk images

## Future Enhancements

1. **Dark Mode Support**
2. **Animation Library Integration**
3. **Component Library Extraction**
4. **Advanced Form Validation UI**
5. **Data Visualization Components**

---

*Design System v1.0 - Created for SAKTI Mini Login System*