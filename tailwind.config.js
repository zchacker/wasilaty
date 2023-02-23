/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php"
  ],
  theme: {
    extend: {
      fontFamily: {
        'Madani': ['Madani', 'sans-serif'],
        'Alexandria' : ['Alexandria' , 'sans-serif'],        
        'cairo': ['Cairo', 'sans-serif'],
      },
      colors:{
        transparent: 'transparent',
        current: 'currentColor',
        'primary' : '#8D71D3',
        'secondary': '#F3D864',
        'dark-purble' : '#3B3C87',
        'light-purble': '#9C9DF4',
        'happy-green' : '#79D45D',
        'dash-bg' : '#FCFCFB',
        'data-dash' : '#E3F2FC',
        'data-dash-items': '#F3D864',
        'normal-btn' : '#49E3D6'
      }
    },
  },
  plugins: [],
}
