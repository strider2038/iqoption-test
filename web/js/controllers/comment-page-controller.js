/**
 * @returns {CommentPageController}
 */
function CommentPageController() {
    this.commentManager = new CommentManager({
        view: new CommentView()
    });
}
