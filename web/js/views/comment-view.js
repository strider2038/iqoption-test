/**
 * @class CommentView
 */
function CommentView() {
    /**
     * @name CommentView#selectors
     * @type Object
     */
    this.selectors = {
        container: '.js-comment',
        body: '.js-comment__body',
        children: '.js-comment__children',
        content: '.js-comment__content',
        delete: '.js-comment__delete',
        edit: '.js-comment__edit',
        reply: '.js-comment__reply',
        save: '.js-comment__save',
        cancel: '.js-comment__cancel',
        errors: '.js-comment__errors',
        toggleTree: '.js-comment__toggle-tree',
        commentForm: '.js-comment-form',
        commentFormSend: '.js-comment-form__send'
    };
    
    /**
     * @name CommentView#states
     * @type Object
     */
    this.states = {
        editable: 'comment_editable'
    };
};

/**
 * Returns comment container
 * @param {jQuery} element
 * @returns {jQuery}
 */
CommentView.prototype.getContainer = function(element) {
    return $(element).closest(this.selectors.container);
};

/**
 * Returns form container
 * @param {jQuery} element
 * @returns {jQuery}
 */
CommentView.prototype.getForm = function(element) {
    return $(element).closest(this.selectors.commentForm);
};

/**
 * Builds model by data attributes and returns it
 * @param {type} $body
 * @returns {CommentModel}
 */
CommentView.prototype.getModel = function($container) {
    var $body = $container.find(this.selectors.body + ':first');
    var $content = $body.find(this.selectors.content);
    var content = '';
    if ($body.hasClass(this.states.editable)) {
        content = $content.find('textarea').val();
    } else {
        content = $content.text();
    }
    
    return new CommentModel({
        id: $container.data('id'),
        content: content
    });
};

/**
 * Builds model by data attributes of the form and returns it
 * @param {jQuery} $form
 * @returns {CommentModel}
 */
CommentView.prototype.getModelFromForm = function($form) {
    return new CommentModel({
        content: $form.find('textarea').val(),
        parentId: $form.data('parentId')
    });
};

/**
 * Turns on/off edit mode for comment block
 * @param {jQuery} $container
 * @param {Boolean} editable
 * @returns {CommentView.prototype}
 */
CommentView.prototype.setEditMode = function($container, editable) {
    var $body = $container.find(this.selectors.body + ':first');
    var $content = $body.find(this.selectors.content);
    if (editable) {
        $body.addClass(this.states.editable);
        var content = $content.text();
        $content.data('content', content);
        $content.html('<textarea class="form-control">' + content + '</textarea>');
    } else {
        $body.removeClass(this.states.editable);
        $content.text($content.data('content'));
        $content.data('content', '');
    }
    return this;
};

/**
 * Renders new content of comment
 * @param {jQuery} $container
 * @param {String} content
 * @returns {CommentView.prototype}
 */
CommentView.prototype.renderContent = function($container, content) {
    var $content = $container.find(this.selectors.content + ':first');
    $content.text(content);
    return this;
};

/**
 * Render validation or request errors
 * @param {jQuery} $body
 * @param {Array} errors
 * @returns {CommentView.prototype}
 */
CommentView.prototype.renderErrors = function($container, errors) {
    var $body = $container.find(this.selectors.errors + ':first');
    errors = errors || [];
    if ($.isPlainObject(errors)) {
        var obj = errors;
        errors = [];
        for (var id in obj) {
            for (var i = 0; i < obj[id].length; i++) {
                errors.push(obj[id][i]);
            }
        }
    }
    $body.html(errors.join('<br>'));
    return this;
};

/**
 * Removes node and all its children from the tree
 * @param {type} $container
 * @returns {CommentView.prototype}
 */
CommentView.prototype.removeTree = function($container) {
    $container.remove();
    return this;
};

/**
 * Removes all reply forms from page
 * @returns {CommentView.prototype}
 */
CommentView.prototype.removeReplyForms = function() {
    $(this.selectors.commentForm + '[data-reply="1"]').remove();
    return this;
};

/**
 * Renders reply form to comment and returns it's container
 * @param {jQuery} $container
 * @returns {jQuery}
 */
CommentView.prototype.renderReplyTo = function($container) {
    this.removeReplyForms();
    var $children = $container.find(this.selectors.children + ':first');
    var parentId = $container.data('id');
    var $form = this.renderForm($children, 'reply', parentId);
    $('html, body').animate({
        scrollTop: $form.offset().top - 60
    }, 500);
    return $form;
};

/**
 * Renders form for creating new comment
 * @param {jQuery} $container
 * @param {String} template
 * @param {Number} parentId
 * @returns {jQuery}
 */
CommentView.prototype.renderForm = function($container, template, parentId) {
    template = template || 'create';
    parentId = parseInt(parentId) || 1;
    $container.append('<div class="js-comment-form ' 
        + (template === 'create' ? '' : 'comment__margin') + '"'
        + ' data-reply="' + (template === 'create' ? 0 : 1) + '"'
        + ' data-parent-id="' + parentId + '">'
        + '<hr><div class="form-group">'
        + '<textarea class="form-control" placeholder="Новый комментарий..."></textarea>' 
        + '</div>'
        + '<div class="form-group text-right text-danger js-comment__errors"></div>'
        + '<div class="form-group text-right">'
        + '<button class="btn btn-primary js-comment-form__send">' 
        + (template === 'create' ? 'Добавить' : 'Ответить')
        + '</button>'
        + '</div>'
        + '</div>');
    return $container.find(this.selectors.commentForm);
};

/**
 * Renders new node replacing reply form or before main form
 * @param {jQuery} $form
 * @param {String} content
 * @returns {CommentView.prototype}
 */
CommentView.prototype.renderNode = function($form, content) {
    if ($form.data('reply')) {
        $form.replaceWith(content);
    } else {
        $form.before(content);
        $form.find('textarea').val('');
    }
    return this;
};

/**
 * Changing visibility of subtree
 * @param {jQuery} element
 * @param {Boolean} open
 * @returns {CommentView.prototype}
 */
CommentView.prototype.toggleTree = function(element, open) {
    var $container = $(element).closest(this.selectors.container);
    var $trigger = $container.find(this.selectors.toggleTree);
    var $children = $container.find(this.selectors.children + ':first');
    var $icon = $trigger.find('.glyphicon');
    var opened = $children.is(':visible');
    if (opened === open) {
        return this;
    }
    $icon.removeClass('glyphicon-chevron-right glyphicon-chevron-down');
    if (opened) {
        $children.hide();
        $icon.addClass('glyphicon-chevron-right');
    } else {
        $children.show();
        $icon.addClass('glyphicon-chevron-down');
    }
    return this;
};