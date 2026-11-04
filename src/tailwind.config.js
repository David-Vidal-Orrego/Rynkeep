import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["var(--fuente)"],
            },
            colors: {
                fondo: "var(--color-fondo)",
                superficie: "var(--color-superficie)",
                borde: "var(--color-borde)",
                texto: "var(--color-texto)",
                suave: "var(--color-suave)",
                primario: "var(--color-primario)",
                "primario-hover": "var(--color-primario-hover)",
                peligro: "var(--color-peligro)",
            },
        },
    },

    plugins: [forms],
};
