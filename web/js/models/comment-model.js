/**
 * @param {Object} attributes
 * @returns {CommentModel}
 */
function CommentModel(attributes) {
    attributes = attributes || {};
    this.id = attributes.id || null;
    this.content = attributes.content || '';
    this.parentId = attributes.parentId || 0;
    this.scenario = attributes.scenario || 'update';
};

/**
 * Saving model to server
 * @param {Function} callback
 * @returns {CommentModel.prototype}
 */
CommentModel.prototype.save = function(callback) {
    $.ajax({
        url: '/site/ajax-comment-save',
        data: {
            id: this.id,
            content: this.content,
            parentId: this.parentId,
            scenario: this.scenario
        },
        type: 'post',
        success: function (response) {
            if (response.ok) {
                callback(true, response.data);
            } else {
                callback(false, response.errors);
            }
        },
        error: function(error) {
            callback(false, {errors: ['Internal server error']});
        }
    });
    return this;
};