(function($) {
    'use strict';
  
    $(document).ready(function() {
      
      function isMobile() {
        return window.innerWidth <= 767;
      }
      
      function fixMobileMenu() {
        if (!isMobile()) return;
        
        const $menuContainer = $('.elementor-nav-menu--dropdown.elementor-nav-menu__container');
        const $header = $('.elementor-location-header, header, .site-header');
        let headerHeight = 60;
        
        if ($header.length) {
          headerHeight = $header.outerHeight();
        }
        
        if ($menuContainer.length) {
          $menuContainer.css({
            'position': 'fixed',
            'top': '50px',
            'left': '0',
            'right': '0',
            'bottom': '0',
            'width': '100vw',
            'height': 'calc(100vh - ' + headerHeight + 'px)',
            'max-height': 'calc(100vh - ' + headerHeight + 'px)',
            'overflow-y': 'auto',
            'overflow-x': 'hidden',
            'background': '#ffffff',
            'z-index': '99999',
            '-webkit-overflow-scrolling': 'touch',
            'overscroll-behavior': 'contain'
          });
          
          const $menu = $menuContainer.find('.elementor-nav-menu');
          $menu.css({
            'height': 'auto',
            'max-height': 'none',
            'overflow': 'visible',
            'padding': '20px 0 40px 0'
          });
        }
      }
      
      function setupMenuToggle() {
        const $toggleButton = $('.elementor-menu-toggle');
        const $menuContainer = $('.elementor-nav-menu--dropdown.elementor-nav-menu__container');
        const $body = $('body');
        let menuIsOpen = false;
        
        if ($toggleButton.length && $menuContainer.length) {
          
          // 清除任何可能存在的錯誤狀態
          $body.removeClass('mobile-menu-active').css({
            'overflow': '',
            'position': '',
            'width': '',
            'height': ''
          });
          
          // 監聽菜單狀態變化
          const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
              if (mutation.type === 'attributes') {
                // 更精確地檢測菜單是否真的可見
                const isVisible = $menuContainer.is(':visible') && 
                                 $menuContainer.css('display') !== 'none' &&
                                 ($menuContainer.css('opacity') === '1' || $menuContainer.css('opacity') === '');
                
                if (isVisible && !menuIsOpen) {
                  // 菜單打開
                  menuIsOpen = true;
                  console.log('Menu opened'); // 調試用
                  fixMobileMenu();
                  $body.addClass('mobile-menu-active').css({
                    'position': 'relative',
                    'width': '100%',
                    'height': '100%',
                    'overflow': 'visible'
                  });
                  $menuContainer.scrollTop(0);
                  
                } else if (!isVisible && menuIsOpen) {
                  // 菜單關閉
                  menuIsOpen = false;
                  console.log('Menu closed'); // 調試用
                  const scrollTop = parseInt($body.css('top')) || 0;
                  $body.removeClass('mobile-menu-active').css({
                    'overflow': '',
                    'position': '',
                    'width': '',
                    'top': '',
                    'height': ''
                  });
                  // 恢復滾動位置
                  if (scrollTop < 0) {
                    $(window).scrollTop(-scrollTop);
                  }
                }
              }
            });
          });
          
          observer.observe($menuContainer[0], {
            attributes: true,
            attributeFilter: ['style', 'class']
          });
          
          // Toggle 按鈕點擊處理
          $toggleButton.on('click.mobileMenuEnhance', function() {
            setTimeout(function() {
              if ($menuContainer.is(':visible')) {
                fixMobileMenu();
              }
            }, 50);
          });
          
          // 點擊外部關閉
          $(document).on('click.mobileMenuClose', function(e) {
            if (menuIsOpen && 
                !$(e.target).closest('.elementor-nav-menu--dropdown, .elementor-menu-toggle').length) {
              $toggleButton.trigger('click');
            }
          });
          
          // ESC 關閉
          $(document).on('keydown.mobileMenuClose', function(e) {
            if (e.keyCode === 27 && menuIsOpen) {
              $toggleButton.trigger('click');
            }
          });
          
          // 菜單項點擊關閉
          $menuContainer.on('click.menuItemClick', '.menu-item a', function() {
            if (!$(this).parent().hasClass('menu-item-has-children')) {
              setTimeout(function() {
                $toggleButton.trigger('click');
              }, 100);
            }
          });
        }
      }
      
      function initMobileMenuFix() {
        if (!isMobile()) return;
        
        // 確保初始狀態正確
        $('body').removeClass('mobile-menu-active').css({
          'overflow': '',
          'position': '',
          'width': '',
          'height': '',
          'top': ''
        });
        
        setupMenuToggle();
      }
      
      // 窗口大小改變處理
      let resizeTimer;
      $(window).on('resize.mobileMenuFix', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
          // 重置所有狀態
          $('body').removeClass('mobile-menu-active').css({
            'overflow': '',
            'position': '',
            'width': '',
            'height': '',
            'top': ''
          });
          
          if (isMobile()) {
            initMobileMenuFix();
          }
        }, 250);
      });
      
      // 頁面載入完成後初始化
      setTimeout(function() {
        initMobileMenuFix();
      }, 300);
      
      // 頁面卸載時清理
      $(window).on('beforeunload', function() {
        $('body').removeClass('mobile-menu-active').css({
          'overflow': '',
          'position': '',
          'width': '',
          'height': '',
          'top': ''
        });
      });
      
    });
    
  })(jQuery);
  