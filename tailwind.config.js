/** Tailwind scans complete class names in views and JS components. */
module.exports = {
  "content": [
    "./resources/views/**/*.php",
    "./app/**/*.php",
    "./public/assets/*.js"
  ],
  "theme": {
    "extend": {
      "colors": {
        "ink": "#141713",
        "paper": "#f6f2e9",
        "accent": "#cf5b40"
      },
      "fontFamily": {
        "display": [
          "Georgia",
          "Noto Bengali",
          "serif"
        ],
        "sans": [
          "Arial",
          "Noto Bengali",
          "Nirmala UI",
          "Vrinda",
          "sans-serif"
        ]
      },
      "keyframes": {
        "hero-in": {
          "from": {
            "opacity": ".5",
            "transform": "translateY(10px)"
          },
          "to": {
            "opacity": "1",
            "transform": "none"
          }
        }
      },
      "animation": {
        "hero-in": "hero-in .55s ease-out both"
      }
    }
  },
  "plugins": [require('tailwindcss/plugin')(({addBase}) => addBase({
    '@font-face': [400, 700].map(weight => ({fontFamily: 'Noto Bengali', fontStyle: 'normal', fontWeight: String(weight), fontDisplay: 'swap', src: `url('/assets/fonts/noto-bengali-${weight}.woff2') format('woff2')`, unicodeRange: 'U+0980-09FF'}))
  }))]
};
