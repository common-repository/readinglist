'use strict';

(function (window, $) {
    window.GhReadingList = function ($wrapper) {
        this.$wrapper = $wrapper;
        // Prevent further code execution if the user is not logged in, e.g. the readinglist is not rendered
        if (this.$wrapper.length === 0) {
            return;
        }

        this.$wrapper.on(
            'click',
            this._selectors.showReadinglistBtn,
            this.showReadingList.bind(this)
        );

        this.$wrapper.on(
            'click',
            '.js-close-list',
            this.closeReadingList.bind(this)
        )

        $(document).on(
            'click',
            this._selectors.addToList,
            this.addItemToList.bind(this)
        );

        this.$wrapper.on(
            'click',
            this._selectors.deleteFromList,
            this.removeItemFromList.bind(this)
        )
        
        $('.reading-list-page').on(
          'click',
          this._selectors.deleteFromPage,
          this.removeItemFromPage.bind(this)
        )
    };

    $.extend(window.GhReadingList.prototype, {
        _selectors: {
            readingList: '.js-reading-list',
            readingListContainer: '.js-readinglist-container',
            showReadinglistBtn: '.js-show-hide-readinglist',
            addToList: '.js-add-to-list',
            deleteFromList: '.js-delete-item',
            deleteFromPage: '.js-remove-page-item',
            emptyListText: '.js-empty-reading-list',
            listCount: '.js-list-count'
        },
        showReadingList: function (e) {
            var $self = $(e.currentTarget);
            $self.addClass('close');
            this.$wrapper.find(this._selectors.readingList).addClass('open');
        },
        closeReadingList: function (e) {
            e.preventDefault();
            this.$wrapper.find(this._selectors.readingList).removeClass('open');
            this.$wrapper.find(this._selectors.showReadinglistBtn).removeClass('close');
        },
        addItemToList: function (e) {
            e.preventDefault();
            var $self = $(e.currentTarget);
            var articleID = $self.data('art-id');
            var that = this;
            $.ajax({
                url: window.ghReadingList.ajaxUrl,
                method: 'POST',
                data: {
                    reading_list_update: window.ghReadingList.nonce,
                    articleID: articleID,
                    listAction: 'add',
                    action: 'gh_readinglist_update_reading_list'
                },
                success: function (data) {
                    var $data = data;
                    var exsists = false;
                    $(that._selectors.emptyListText).remove();
                    // Check children for the added ID to prevent duplicate entries
                    if ($(that._selectors.readingListContainer).children().length > 0) {
                        $(that._selectors.readingListContainer).children().each(function (key, value) {
                            if ($(value).data('art-id') === $data.id) {
                                exsists = true;
                            }
                        });
                    }

                    if (!exsists) {
                        var element = '<li class="item mb-1" data-art-id="' + data.id + '">';
                        var title = ($data.title.length >= 40) ? $data.title.substr(0, 40) + '...' : $data.title;
                        element += '<a href="' + $data.postLink + '">' + title + '</a><span class="rl-icon-trash js-delete-item"></span></li>';
                        if ($(that._selectors.readingListContainer).children().length >= 1 ) {
                            $(that._selectors.readingListContainer).prepend(element);
                        } else {
                            $(that._selectors.readingListContainer).append(element);
                        }
                    }
                    $(that._selectors.listCount).text($(that._selectors.readingListContainer).children().length);

                    if (!$(that._selectors.showReadinglistBtn).hasClass('close')) {
                        $(that._selectors.showReadinglistBtn).addClass('close');
                        $(that._selectors.readingList).addClass('open');
                    }

                }
            })
        },
        removeItemFromList: function (e) {
            e.preventDefault();
            var self = $(e.currentTarget);
            var that = this;
            var articleID = self.closest('li').data('art-id');
            self.prev().addClass('item-fadeout');
            $.ajax({
                url: window.ghReadingList.ajaxUrl,
                method: 'POST',
                data: {
                    reading_list_update: window.ghReadingList.nonce,
                    articleID: articleID,
                    listAction: 'drop',
                    action: 'gh_readinglist_update_reading_list'
                },
                success: function (data) {
                    var $data = data;
                    // Check children for the added ID to prevent duplicate entries
                    $(that._selectors.readingListContainer).children().each(function (key, value) {
                        if ($(value).data('art-id') === parseInt($data.id)) {
                            $(value).remove();
                        }
                    });
                    $(that._selectors.listCount).text($(that._selectors.readingListContainer).children().length);
                }
            });
        },
        removeItemFromPage: function (e) {
            e.preventDefault();
            var self = $(e.currentTarget);
            var articleID = self.closest('.list-item').data('art-id');
            self.parent('tr').addClass('item-fadeout');
            $.ajax({
                url: window.ghReadingList.ajaxUrl,
                method: 'POST',
                data: {
                    reading_list_update: window.ghReadingList.nonce,
                    articleID: articleID,
                    listAction: 'drop',
                    action: 'gh_readinglist_update_reading_list'
                },
                success: function (data) {
                    var $data = data;
                    // Check children for the added ID to prevent duplicate entries
                    $('.reading-list-page tbody').children().each(function (key, value) {
                        if ($(value).data('art-id') === parseInt($data.id)) {
                            $(value).remove();
                        }
                    });
                }
            });
        }
    });

})(window, jQuery);

jQuery(document).ready(function ($) {
    var $wrapper = $('.readinglist-wrapper');
    new GhReadingList($wrapper);
});
