# Design System Specification: The Fluid Precision Framework

## 1. Overview & Creative North Star
**Creative North Star: "The Digital Private Banker"**

This design system moves away from the "utility-first" clutter of traditional fintech. Instead, it adopts an editorial, high-end aesthetic characterized by **Atmospheric Depth** and **Asymmetric Balance**. We are not building a spreadsheet; we are building a premium service. 

The system achieves a "Modern, Clean, and Lightweight" feel by prioritizing negative space over structural lines. By utilizing a sophisticated layering of soft grays and intentional typographic scaling, the UI feels fast, airy, and expensive. The design breaks the "template" look by using exaggerated type scales (Manrope for displays) and overlapping surface elements that suggest a tactile, physical presence.

---

## 2. Colors & Surface Philosophy
The palette is rooted in a high-contrast "Soft Gray" foundation, using vibrant semantic accents to denote financial health and status.

### The "No-Line" Rule
**Explicit Instruction:** 1px solid borders are strictly prohibited for sectioning or containment. 
Boundaries must be defined through **Background Color Shifts** or **Tonal Transitions**. For example, a card should never have a border; it should be a `surface-container-lowest` (#FFFFFF) element sitting on a `surface` (#F8F9FA) background.

### Surface Hierarchy (Nesting)
We treat the UI as a series of nested physical layers.
*   **Base Layer:** `surface` (#F8F9FA) – The canvas.
*   **Section Layer:** `surface-container-low` (#F3F4F5) – For grouping secondary content.
*   **Primary Interaction Layer:** `surface-container-lowest` (#FFFFFF) – For main summary cards and input fields.
*   **The Glass Rule:** For floating elements (like Bottom Nav or Quick Replies), use `surface` at 80% opacity with a `20px` backdrop-blur. This ensures the financial data "bleeds" through the UI, maintaining a sense of speed and transparency.

### Signature Textures
Main CTAs (Primary) must not be flat. Apply a subtle linear gradient: 
*   **From:** `primary_container` (#1A73E8) 
*   **To:** `primary` (#005BC0) 
*   **Direction:** 135 degrees. 
This adds "soul" and depth, preventing the app from looking like a generic wireframe.

---

## 3. Typography: The Editorial Edge
We employ a dual-font strategy to balance authority with readability.

*   **Display & Headlines (Manrope):** Use Manrope for all headers. It is wider and more modern than standard sans-serifs, providing an "Editorial" feel.
    *   *Display-LG (3.5rem):* Reserved for account balances and hero numbers.
    *   *Headline-SM (1.5rem):* For section titles.
*   **Body & Labels (Inter):** Use Inter for all functional data. Its high x-height ensures maximum legibility at small sizes during fast scrolling.
    *   *Body-MD (0.875rem):* Default for chat bubbles and transaction details.
    *   *Label-SM (0.6875rem):* For micro-data (timestamps, metadata). Use `letter-spacing: 0.05em` for a premium, tracked-out look.

---

## 4. Elevation & Depth
Depth is achieved through **Tonal Layering**, not structural shadows.

*   **The Layering Principle:** To lift an element, move one step "down" the surface-container scale. A `surface-container-highest` element will naturally "pop" against a `surface` background without needing a drop shadow.
*   **Ambient Shadows:** Use only for high-level floating modals. 
    *   *Values:* `0px 24px 48px rgba(25, 28, 29, 0.06)`. 
    *   *Note:* The shadow is a tinted version of `on_surface`, never pure black.
*   **The Ghost Border Fallback:** If a border is required for accessibility, use `outline_variant` at **15% opacity**. It must feel like a suggestion of a line, not a hard stop.

---

## 5. Component Architecture

### Summary Cards
*   **Design:** No borders. Background: `surface-container-lowest` (#FFFFFF).
*   **Rounding:** `lg` (2rem) for a friendly, soft-touch feel.
*   **Spacing:** `6` (1.5rem) internal padding.
*   **Visual Logic:** Use `primary` (#1A73E8) for trend lines and `secondary` (#006E2C) for positive growth indicators.

### Chat & Messaging
*   **User Bubbles:** Background: `primary` (#005BC0), Text: `on_primary` (#FFFFFF). Roundedness: `DEFAULT` (1rem), but `0.25rem` on the bottom-right corner to point to the sender.
*   **System/Bot Bubbles:** Background: `surface-container-high` (#E7E8E9). No border.
*   **Quick Reply Pills:** Use `full` (9999px) rounding. Background: `surface-container-lowest` (#FFFFFF) with a `Ghost Border`.

### Bottom Navigation Bar
*   **Structure:** 3 Icons only (Home, Insights, Profile).
*   **Style:** A "Floating Dock" style. Do not span the full width of the screen. Inset it by `8` (2rem) on each side.
*   **Effect:** Glassmorphism (80% `surface` + backdrop-blur).
*   **Active State:** Use a `primary` (#1A73E8) dot indicator below the icon, rather than shifting the icon color.

### Input Fields
*   **Styling:** Forbid dividers. Inputs are `surface-container-low` (#F3F4F5) blocks.
*   **Focus State:** Transition the background to `surface-container-lowest` (#FFFFFF) and add a `2px` "Ghost Border" of the `primary` color.

---

## 6. Do’s and Don'ts

### Do
*   **DO** use white space as a separator. If you think you need a line, add `1.5rem` of vertical space instead.
*   **DO** use `secondary` (#006E2C) for all "Success" and "Money In" states to build a positive psychological association.
*   **DO** use `tertiary_fixed_dim` (#FBBC06) for warnings—it provides a sophisticated "gold" tone rather than a jarring safety orange.

### Don’t
*   **DON'T** use 100% opaque black for text. Use `on_surface` (#191C1D) to maintain the soft, premium aesthetic.
*   **DON'T** use standard Material Design "elevated" cards with heavy shadows. This system relies on color-blocking for hierarchy.
*   **DON'T** use "Default" Inter weights. Use *Medium (500)* for labels and *Semi-Bold (600)* for titles to ensure the hierarchy feels intentional.

---

## 7. Spacing & Rhythm
We follow a strict **8pt Grid**, but we apply it asymmetrically. 
*   **Horizontal Margins:** Always `8` (2rem) to create a wide, luxurious frame for the content.
*   **Vertical Rhythm:** Use `10` (2.5rem) between major sections to allow the eye to rest. This "breathing room" is what defines the "Lightweight" feel.