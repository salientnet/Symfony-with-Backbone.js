App.Views.EditEntry = Backbone.View.extend({
  template: _.template($('#edit-entry-modal').html()),
  initialize: function() {
    this.model.on('change', this.render, this);
  },
  render: function() {
    $(this.el).html(this.template({'entry' : this.model}));
    return this;
  }
});
