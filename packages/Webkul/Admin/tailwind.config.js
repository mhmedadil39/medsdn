/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./src/Resources/**/*.blade.php", "./src/Resources/**/*.js"],

    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1920px",
            },

            padding: {
                DEFAULT: "16px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1920px",
        },

        extend: {
            colors: {
                // Brand color with full palette
                brandColor: {
                    DEFAULT: '#0066CC',
                    50: '#E6F2FF',
                    100: '#CCE5FF',
                    200: '#99CCFF',
                    300: '#66B2FF',
                    400: '#3399FF',
                    500: '#0066CC',
                    600: '#0052A3',
                    700: '#003D7A',
                    800: '#002952',
                    900: '#001429',
                },
                
                // Existing colors for backward compatibility
                darkGreen: '#40994A',
                darkBlue: '#0044F2',
                darkPink: '#F85156',
            },

            fontFamily: {
                inter: ['Inter'],
                icon: ['icomoon', 'sans-serif']
            },
            
            transitionDuration: {
                '80': '80ms',
            },
            
            zIndex: {
                '10001': '10001',
                '10002': '10002',
                '10003': '10003',
            },
        },
    },
    
    darkMode: 'class',

    plugins: [],

    safelist: [
        {
            pattern: /icon-/,
        },
        {
            pattern: /bg-brandColor/,
        },
        {
            pattern: /text-brandColor/,
        },
        {
            pattern: /border-brandColor/,
        },
    ]
};
