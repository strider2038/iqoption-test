/**
 * @param {Object} config
 * @returns {CommentManager}
 */
function CommentManager(config) {
    config = config || {};
    if (typeof config.view !== 'object') {
        throw new Error('Undefined view for CommentManager');
    }
    
    /**
     * @name CommentManager#view
     * @type CommentView
     */
    this.view = config.view;
    
    /**
     * State of AJAX request
     * @name CommentManager#busy
     * @type Boolean
     */
    this.busy = false;
    
    // renders main form
    this.view.renderForm($('.js-comment-wrapper'));
    
    /**
     * @type CommentManager
     */
    var self = this;
    
    /**
     * Turning on edit mode for comment block
     */
    $(document).on('click', self.view.selectors.edit, function() {
        var $comment = self.view.getContainer(this);
        self.view.setEditMode($comment, true);
    });
    
    /**
     * Turning off edit mode for comment block
     */
    $(document).on('click', self.view.selectors.cancel, function() {
        var $comment = self.view.getContainer(this);
        self.view.setEditMode($comment, false);
        self.view.renderErrors($comment);
    });
    
    /**
     * Updating existing comment
     */
    $(document).on('click', self.view.selectors.save, function() {
        if (self.busy) {
            return;
        }
        var $comment = self.view.getContainer(this);
        var model = self.view.getModel($comment);
        
        self.busy = true;
        model.scenario = 'update';
        self.view.renderErrors($comment);
        model.save(function(success, data) {
            self.busy = false;
            if (success) {
                self.view.setEditMode($comment, false);
                self.view.renderContent($comment, data.content);
            } else {
                self.view.renderErrors($comment, data);
            }
        });
    });
    
    /**
     * Deleting comment
     */
    $(document).on('click', self.view.selectors.delete, function() {
        if (self.busy) {
            return;
        }
        var $comment = self.view.getContainer(this);
        yii.confirm('Вы уверены, что хотите удалить этот и все вложенные комментарии?', function() {
            var model = self.view.getModel($comment);

            self.busy = true;
            model.scenario = 'delete';
            model.save(function(success, data) {
                self.busy = false;
                if (success) {
                    self.view.removeTree($comment);
                } else {
                    self.view.renderErrors($comment, data);
                }
            });
        });
    });
    
    /**
     * Rendering reply form
     */
    $(document).on('click', self.view.selectors.reply, function() {
        var $comment = self.view.getContainer(this);
        self.view.toggleTree(this, true);
        self.view.renderReplyTo($comment);
    });
    
    /**
     * Creating new comment
     */
    $(document).on('click', self.view.selectors.commentFormSend, function() {
        var $form = self.view.getForm(this);
        var model = self.view.getModelFromForm($form);
        self.view.renderErrors($form);
        
        self.busy = true;
        model.scenario = 'create';
        model.save(function(success, data) {
            self.busy = false;
            if (success) {
                self.view.renderNode($form, data.content);
            } else {
                self.view.renderErrors($form, data);
            }
        });
    });
    
    /**
     * Changing visibility of subtree
     */
    $(document).on('click', self.view.selectors.toggleTree, function() {
        self.view.toggleTree(this);
    });
}