/** @type {import("tailwindcss").Config} */
export default {
    darkMode: "selector",
    safelist: [
        {
            pattern: /^from-\[#[0-9A-Fa-f]{6}]$/,
        },
    ],
    content: [
        "./resources/**/*.{js,vue,blade.php}",
        "./app/**/*.php",

        // core Laravel views you might still use
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",

        // **MODULES**: all blade/js/vue in each module
        "./Modules/**/resources/views/**/*.blade.php",
        "./Modules/**/resources/js/**/*.{js,vue}",
    ],
    theme: {
        extend: {
            colors: {
                primary: "var(--color-primary)",
                mirage: "#141624",
                haiti: "#16182C",
                cloud: "#2b2e53",
            },
            fontFamily: {
                poppins: "'Poppins', Verdana, sans-serif",
            },
        },
    },
    plugins: [require("@tailwindcss/typography"), require("flowbite/plugin")],
};
