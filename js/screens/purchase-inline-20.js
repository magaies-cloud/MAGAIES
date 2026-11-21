(function initScreenMaster7ThemeEarly() {
  var getCookieVal = function(name) {
    var m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
  };
  var raw = getCookieVal('magaies_active_theme') ||
            localStorage.getItem('magaies_active_theme') || 
            localStorage.getItem('magaiees_global_theme') || 
            localStorage.getItem('magaiees_mobile_theme') || 
            localStorage.getItem('ui_theme') || 
            'warm_cream';
  var rawTone = getCookieVal('magaies_light_tone') ||
                localStorage.getItem('magaies_light_tone') || 
                localStorage.getItem('pur_light_tone_palette') || 
                'ivory_default';
  var aliasMap = {
    'dark_midnight': 'midnight_obsidian', 'dark_mocha': 'mocha_brown', 'dark_oled': 'pure_oled', 'dark_slate': 'slate_twilight',
    'warm-alabaster': 'warm_cream', 'beige': 'warm_cream', 'ivory': 'warm_cream', 'light-linen': 'warm_cream', 'ivory_default': 'warm_cream',
    'royal_bronze': 'warm_cream', 'platinum_gray': 'exec_gray', 'crystal_silver': 'ultra_white', 'sage_pearl': 'warm_cream', 'sky_alabaster': 'ultra_white', 'warm_mocha_dark': 'warm_cream', 'deep_slate_dark': 'exec_gray',
    'pure-platinum': 'ultra_white', 'offwhite': 'ultra_white', 'white': 'ultra_white', 'light': 'ultra_white',
    'pearl-platinum': 'exec_gray', 'nordic-paper': 'warm_cream', 'solar_contrast': 'warm_cream',
    'midnight-executive': 'midnight_obsidian', 'navy-kohl': 'midnight_obsidian', 'royal-blue': 'midnight_obsidian', 'ocean-navy': 'midnight_obsidian', 'navy': 'midnight_obsidian',
    'sepia-brown': 'mocha_brown', 'desert-mocha': 'mocha_brown', 'brown': 'mocha_brown',
    'obsidian-charcoal': 'pure_oled', 'midnight': 'pure_oled', 'black': 'pure_oled', 'dark': 'pure_oled',
    'twilight-indigo': 'slate_twilight', 'emerald-forest': 'slate_twilight', 'forest': 'slate_twilight'
  };
  var activeKey = (rawTone && aliasMap[rawTone]) ? aliasMap[rawTone] : (aliasMap[raw] || raw);
  var valid7 = ['warm_cream', 'ultra_white', 'exec_gray', 'midnight_obsidian', 'mocha_brown', 'pure_oled', 'slate_twilight'];
  if (!valid7.includes(activeKey)) activeKey = 'warm_cream';
  var isLight = ['warm_cream', 'ultra_white', 'exec_gray'].includes(activeKey);
  if (!isLight) rawTone = 'dark_midnight';

  document.documentElement.setAttribute('data-theme', activeKey);
  document.documentElement.setAttribute('data-light-tone', rawTone);
  if (isLight) { document.documentElement.classList.add('light-theme'); document.documentElement.classList.remove('dark-theme'); }
  else { document.documentElement.classList.remove('light-theme'); document.documentElement.classList.add('dark-theme'); }

  document.addEventListener("DOMContentLoaded", function() {
    if (document.body) {
      document.body.setAttribute('data-theme', activeKey);
      document.body.setAttribute('data-light-tone', rawTone);
      if (isLight) { document.body.classList.add('light-theme'); document.body.classList.remove('dark-theme'); }
      else { document.body.classList.remove('light-theme'); document.body.classList.add('dark-theme'); }
    }
  });
})();
