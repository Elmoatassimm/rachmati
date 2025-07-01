module.exports = {
  content: [
    "./resources/**/*.{js,ts,jsx,tsx,blade.php}",
    "./resources/**/*.{vue,svelte}",
    "./app/**/*.php",
    "./components/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        arabic: ['Noto Sans Arabic', 'Arial', 'sans-serif'],
      },
    },
  },
  plugins: [],
} 