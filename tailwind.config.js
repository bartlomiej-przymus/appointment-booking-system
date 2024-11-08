/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme';
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Raleway', ...defaultTheme.fontFamily.sans],
                serif: ['Alice', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                primary: '#fff',
                secondary: '#ffe4ef',
                accentColor: '#f00',
                bkgColor: '#100',
                // ...
            }
        },
    },

  plugins: [],
}

