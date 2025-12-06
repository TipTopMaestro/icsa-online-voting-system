# Sidebar Navigation Color Customization Guide

## Location
File: esources/css/app.css

## Sidebar Color Variables

### Light Mode (Lines 118-126)
The sidebar colors for light theme are defined in the :root selector:

\\\css
--sidebar-background: hsl(0 0% 98%);           /* Sidebar background color */
--sidebar-foreground: hsl(240 5.3% 26.1%);    /* Default text color */
--sidebar-primary: hsl(0 0% 10%);             /* Primary elements color */
--sidebar-primary-foreground: hsl(0 0% 98%);  /* Primary text color */
--sidebar-accent: hsl(0 0% 94%);              /* Active/hover background */
--sidebar-accent-foreground: hsl(0 0% 30%);   /* Active/hover text color */
--sidebar-border: hsl(0 0% 91%);              /* Border color */
--sidebar-ring: hsl(217.2 91.2% 59.8%);       /* Focus ring color */
\\\

### Dark Mode (Lines 154-162)
The sidebar colors for dark theme are in the \.dark\ selector:

\\\css
--sidebar-background: hsl(0 0% 7%);           /* Sidebar background color */
--sidebar-foreground: hsl(0 0% 95.9%);        /* Default text color */
--sidebar-primary: hsl(360, 100%, 100%);      /* Primary elements color */
--sidebar-primary-foreground: hsl(0 0% 100%); /* Primary text color */
--sidebar-accent: hsl(0 0% 15.9%);            /* Active/hover background */
--sidebar-accent-foreground: hsl(240 4.8% 95.9%); /* Active/hover text color */
--sidebar-border: hsl(0 0% 15.9%);            /* Border color */
--sidebar-ring: hsl(217.2 91.2% 59.8%);       /* Focus ring color */
\\\

## What Each Variable Controls

- **--sidebar-background**: The main background of the sidebar
- **--sidebar-foreground**: Default text and icon color (inactive state)
- **--sidebar-accent**: Background color when menu item is active or hovered
- **--sidebar-accent-foreground**: Text and icon color when active or hovered
- **--sidebar-primary**: Special highlight color
- **--sidebar-ring**: Focus outline color when navigating with keyboard
- **--sidebar-border**: Divider lines between sections

## Example Customizations

### Purple Theme
\\\css
/* Light Mode */
--sidebar-accent: hsl(270 80% 95%);              /* Light purple bg on hover/active */
--sidebar-accent-foreground: hsl(270 70% 40%);   /* Purple text on hover/active */
--sidebar-ring: hsl(270 70% 50%);                /* Purple focus ring */

/* Dark Mode */
--sidebar-accent: hsl(270 70% 20%);              /* Dark purple bg on hover/active */
--sidebar-accent-foreground: hsl(270 80% 85%);   /* Light purple text on hover/active */
\\\

### Blue Theme
\\\css
/* Light Mode */
--sidebar-accent: hsl(217 90% 95%);              /* Light blue bg on hover/active */
--sidebar-accent-foreground: hsl(217 70% 40%);   /* Blue text on hover/active */

/* Dark Mode */
--sidebar-accent: hsl(217 70% 20%);              /* Dark blue bg on hover/active */
--sidebar-accent-foreground: hsl(217 80% 85%);   /* Light blue text on hover/active */
\\\

### Green Theme
\\\css
/* Light Mode */
--sidebar-accent: hsl(142 76% 93%);              /* Light green bg on hover/active */
--sidebar-accent-foreground: hsl(142 70% 35%);   /* Green text on hover/active */

/* Dark Mode */
--sidebar-accent: hsl(142 70% 18%);              /* Dark green bg on hover/active */
--sidebar-accent-foreground: hsl(142 76% 80%);   /* Light green text on hover/active */
\\\

## HSL Color Format
Colors use HSL format: \hsl(hue saturation% lightness%)\
- **Hue**: 0-360 (0=red, 120=green, 240=blue, 270=purple)
- **Saturation**: 0-100% (0=gray, 100=vibrant)
- **Lightness**: 0-100% (0=black, 50=normal, 100=white)

## Quick Tips
1. Keep lightness high (90-95%) for light mode hover backgrounds
2. Keep lightness low (15-20%) for dark mode hover backgrounds
3. Ensure good contrast between text and background (WCAG standards)
4. Test both light and dark modes after changes
