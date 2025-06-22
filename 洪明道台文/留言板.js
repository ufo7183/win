jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * 發送留言功能
     */
    $('.angminde-submit-btn').on('click', function() {
        var message = $('.angminde-message-input').val().trim();
        
        if (message === '') {
            alert('請輸入留言內容');
            return;
        }
        
        if (message.length > 1000) {
            alert('留言內容不能超過1000字');
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).text('發送中...');
        
        $.ajax({
            url: angminde_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'add_comment',
                message: message,
                nonce: angminde_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.angminde-message-input').val('');
                    // 重新載入頁面顯示新留言
                    if (response.data.reload) {
                        location.reload();
                    }
                } else {
                    alert('錯誤：' + response.data);
                }
            },
            error: function() {
                alert('網路錯誤，請稍後再試');
            },
            complete: function() {
                $button.prop('disabled', false).text('發送');
            }
        });
    });
    
    /**
     * 回覆功能
     */
    $(document).on('click', '.angminde-reply-submit', function() {
        var $this = $(this);
        var parentId = $this.data('parent-id');
        var message = $this.siblings('.angminde-reply-input-wrapper').find('.angminde-reply-input').val().trim();
        
        if (message === '') {
            alert('請輸入回覆內容');
            return;
        }
        
        $this.prop('disabled', true).text('回覆中...');
        
        $.ajax({
            url: angminde_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'add_comment',
                message: message,
                parent_id: parentId,
                nonce: angminde_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $this.siblings('.angminde-reply-input').val('');
                    if (response.data.reload) {
                        location.reload();
                    }
                } else {
                    alert('錯誤：' + response.data);
                }
            },
            error: function() {
                alert('網路錯誤，請稍後再試');
            },
            complete: function() {
                $this.prop('disabled', false).text('回覆');
            }
        });
    });
    
    /**
     * 按讚功能
     */
    $('.angminde-like-btn:not(.disabled)').on('click', function() {
        if (!angminde_ajax.is_logged_in) {
            window.location.href = angminde_ajax.login_url;
            return;
        }
        
        var $this = $(this);
        var messageId = $this.data('message-id');
        var $count = $this.find('.like-count');
        
        $.ajax({
            url: angminde_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'like_comment',
                message_id: messageId,
                nonce: angminde_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $count.text(response.data.like_count);
                    
                    // 添加視覺反饋
                    if (response.data.action === 'liked') {
                        $this.addClass('liked');
                    } else {
                        $this.removeClass('liked');
                    }
                }
            },
            error: function() {
                alert('操作失敗，請稍後再試');
            }
        });
    });
    
    /**
     * 展開/收合回覆功能
     */
    $('.angminde-message-item').each(function() {
        var $messageItem = $(this);
        var $replies = $messageItem.find('> .angminde-replies'); // 只選擇直接子元素
        var $replyForm = $messageItem.find('> .angminde-reply-form'); // 只選擇直接子元素
        var $replyCount = $messageItem.find('> .angminde-message-footer .angminde-reply-count'); // 更精確的選擇器
        
        // 點擊主留言展開/收合回覆
        $messageItem.find('> .angminde-message-main').on('click', function(e) {
            // 避免點擊按讚按鈕時觸發
            if ($(e.target).closest('.angminde-like-btn').length > 0) {
                return;
            }
            
            // 只切換當前點擊的留言的回覆
            if ($replies.length > 0) {
                $replies.slideToggle(300);
                $messageItem.toggleClass('expanded');
            }
            
            // 如果是管理員，也顯示回覆表單
            if ($replyForm.length > 0) {
                $replyForm.slideToggle(300);
            }
            
            // 阻止事件冒泡，避免觸發父層的點擊事件
            e.stopPropagation();
        });
        
        // 點擊回覆計數也可以展開
        $replyCount.on('click', function(e) {
            if ($replies.length > 0) {
                $replies.slideToggle(300);
                $messageItem.toggleClass('expanded');
                
                // 如果是管理員，也顯示回覆表單
                if ($replyForm.length > 0) {
                    $replyForm.slideToggle(300);
                }
            }
            e.stopPropagation(); // 阻止事件冒泡
        });
    });
    
    /**
     * 輸入框自動調整高度
     */
    $('.angminde-message-input, .angminde-reply-input').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    /**
     * Enter鍵發送留言（Shift+Enter換行）
     */
    $('.angminde-message-input').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('.angminde-submit-btn').click();
        }
    });
    
    $('.angminde-reply-input').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $(this).siblings('.angminde-reply-submit').click();
        }
    });
    
    /**
     * 頭像上傳功能
     */
    function initAvatarUpload() {
        // 添加頭像上傳按鈕到用戶帳戶頁面
        if ($('.woocommerce-MyAccount-content').length > 0) {
            var uploadHTML = '<div class="angminde-avatar-upload">' +
                '<h3>自訂頭像</h3>' +
                '<input type="file" id="avatar-upload" accept="image/*" style="display:none;">' +
                '<button type="button" id="upload-avatar-btn">上傳頭像</button>' +
                '<div id="avatar-preview"></div>' +
                '</div>';
            
            $('.woocommerce-MyAccount-content').prepend(uploadHTML);
            
            $('#upload-avatar-btn').on('click', function() {
                $('#avatar-upload').click();
            });
            
            $('#avatar-upload').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var formData = new FormData();
                    formData.append('avatar', file);
                    formData.append('action', 'upload_avatar');
                    formData.append('nonce', angminde_ajax.nonce);
                    
                    $.ajax({
                        url: angminde_ajax.ajax_url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                $('#avatar-preview').html('<img src="' + response.data.avatar_url + '" style="width:100px;height:100px;border-radius:50%;">');
                                alert('頭像上傳成功！');
                            } else {
                                alert('上傳失敗：' + response.data);
                            }
                        },
                        error: function() {
                            alert('上傳失敗，請稍後再試');
                        }
                    });
                }
            });
        }
    }
    
    // 初始化頭像上傳功能
    initAvatarUpload();
    
    /**
     * 滾動載入動畫
     */
    function animateOnScroll() {
        $('.angminde-message-item').each(function() {
            var $this = $(this);
            var top = $this.offset().top;
            var height = $this.outerHeight();
            var windowTop = $(window).scrollTop();
            var windowHeight = $(window).height();
            
            if (top < windowTop + windowHeight - 100 && top + height > windowTop) {
                $this.addClass('visible');
            }
        });
    }
    
    // 綁定滾動事件
    $(window).on('scroll', animateOnScroll);
    animateOnScroll(); // 初始執行
});
