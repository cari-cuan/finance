# Design System Document: Precision Finance

## 1. Overview & Creative North Star: "The Kinetic Ledger"
This design system is built for the high-velocity world of modern finance. The Creative North Star is **The Kinetic Ledger**—a visual philosophy that prioritizes high information density without sacrificing elegance. 

Unlike traditional "clunky" banking apps, this system avoids heavy containers and rigid borders. Instead, it utilizes **Tonal Depth** and **Intentional Asymmetry** to guide the eye. We break the "template" look by treating the UI as a living document: using tight typographic scales and subtle background shifts to create a "light-speed" feel. The experience should feel like a high-end physical ledger reimagined for a glass screen—precise, authoritative, and impossibly fast.

## 2. Colors: Tonal Architecture
The palette is rooted in a professional blue (#1A73E8), but its luxury comes from how it interacts with neutral surfaces.

### The "No-Line" Rule
**Explicit Instruction:** 1px solid borders are prohibited for sectioning. Use background shifts to define boundaries. A `surface-container-low` card sitting on a `surface` background is the standard for separation.

### Surface Hierarchy & Nesting
Treat the UI as stacked sheets of fine paper. 
- **Base Layer:** `surface` (#f8f9fa) for the overall background.
- **Secondary Layer:** `surface-container-low` (#f3f4f5) for large content areas.
- **Top Layer:** `surface-container-lowest` (#ffffff) for primary cards and interaction points.
- **Nesting:** Always place a "lighter" surface on a "darker" background to create a natural, soft lift.

### The Glass & Gradient Rule
To move beyond a generic "Material" feel:
- **Glassmorphism:** For floating headers or navigation bars, use `surface` at 80% opacity with a `20px` backdrop blur.
- **Signature Textures:** For primary CTAs and Hero sections, do not use flat #1A73E8. Apply a subtle linear gradient from `primary_container` (#1a73e8) to `primary` (#005bbf) at a 135° angle to add "soul" and depth.

## 3. Typography: The Editorial Edge
We use **Manrope** for its technical precision and modern geometric forms. The goal is "Small but Readable."

*   **Display (Large/Medium/Small):** Used for large balance amounts. These should feel like a statement. Use `display-sm` (2.25rem) for main account balances to maintain high density.
*   **Headline & Title:** Use `title-sm` (1rem) for section headers. It provides enough weight to anchor a section without wasting vertical space.
*   **Body (Large/Medium/Small):** The workhorse. Use `body-md` (0.875rem) for most transactional data. 
*   **Labels:** `label-md` (0.75rem) and `label-sm` (0.6875rem) are critical for "metadata" like timestamps or status indicators.

**Editorial Tip:** Use `on_surface_variant` (#414754) for labels to create a sophisticated contrast against the high-intensity `on_surface` (#191c1d) used for primary figures.

## 4. Elevation & Depth: Tonal Layering
Traditional drop shadows are too heavy for a "fast" finance app. We use **Tonal Layering**.

*   **The Layering Principle:** Depth is achieved by "stacking." A `surface-container-lowest` card placed on `surface-container` provides all the visual affordance needed.
*   **Ambient Shadows:** For floating elements (like a "Add Transaction" FAB), use an extra-diffused shadow: `0px 12px 32px rgba(25, 28, 29, 0.06)`. The tint is derived from `on_surface`, not pure black.
*   **The "Ghost Border" Fallback:** If a border is required for accessibility, use the `outline_variant` token at **15% opacity**. Never use a 100% opaque border.
*   **Edge Softness:** Use the **xl (0.75rem / 12px)** radius for cards and **lg (0.5rem / 8px)** for smaller components. Avoid the "pill" shape; we want the UI to feel architectural, not bubbly.

## 5. Components: High-Density Primitives

### Buttons
- **Primary:** Gradient-filled (`primary_container` to `primary`). Radius: `0.5rem`. No shadow.
- **Secondary:** `surface-container-high` background with `on_surface` text. This feels more "integrated" than a bordered button.
- **Tertiary:** Text-only, using `primary` (#005bbf) for the label.

### Input Fields
- **Styling:** Use a "filled" style with `surface-container-low`. No bottom line.
- **States:** On focus, the background shifts to `surface-container-high` with a 2px `primary` ghost border (20% opacity).

### Cards & Lists (The Compact Rule)
- **NO DIVIDERS:** Do not use lines between list items. Use a `0.5rem` (Spacing 2.5) vertical gap or a subtle background color toggle between items.
- **Density:** Use Spacing `3` (0.6rem) for internal card padding to keep information grouped tightly.

### Finance Specific: Sparklines & Data
- **The "Pulse" Component:** A micro-graph (sparkline) used within list items. Stroke width should be `1.5px` using `primary` for growth or `error` for loss.

## 6. Do's and Don'ts

### Do
*   **DO** use whitespace as a separator. 
*   **DO** use "Manrope" in semi-bold for currency symbols to make them feel like icons.
*   **DO** utilize the `surface-container` tiers to create a "nested" look for transaction history within an account view.
*   **DO** keep the radius between 8px and 12px to maintain a professional, slightly technical aesthetic.

### Don't
*   **DON'T** use the "pill" shape for buttons; it feels too consumer-casual for high-end finance.
*   **DON'T** use pure #000000 for text. Use `on_surface` (#191c1d) to keep the "Light" feel.
*   **DON'T** use 1px dividers. They add visual "noise" that slows down the user's ability to scan data.
*   **DON'T** use standard Material Design elevation shadows. Stick to Tonal Layering.