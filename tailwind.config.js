/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './app/Http/Controllers/**/*.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        'verde-claro': '#85B093',
        'verde-medio': '#568F7C',
        'verde-oscuro': '#326D6C',
        'azul-verdoso': '#173C4C',
        'azul-noche': '#07142B',
        'negro-azulado': '#000009',
      },
    },
  },
  plugins: [],
};
