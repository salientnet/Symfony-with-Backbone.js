App.Views.EntriesIndex = Backbone.View.extend({
  template: _.template($('#entries-index').html()),
  events: {
    'click #edit_entry': 'editEntry'
  },
  initialize: function() {
    this.collection.on('reset', this.render, this);
    this.collection.on('add', this.appendEntry, this);
    $('.alert .close').live("click", function(e) {
        $(this).parent().hide();
    });
  },

  render: function() {
    $(this.el).html(this.template());
    this.collection.each(function (entry){
      var now = new Date(entry.get('user_birthday'));
      var month = (now.getMonth() + 1);
      var day = now.getDate();
      if(month < 10)
          month = "0" + month;
      if(day < 10)
          day = "0" + day;
      var birthday = now.getFullYear() + '-' + month + '-' + day;
      entry.set('user_birthday', birthday);
      this.appendEntry(entry);
    },this);
    return this;
  },
  appendEntry: function(entry) {
    view = new App.Views.Entry({'model' : entry});
    $('#entries').append(view.render().el);
  },
  editEntry: function(e) {
    e.preventDefault();
    var that = this;
    var entry = this.collection.get(2);
    var now = new Date(entry.get('user_birthday'));
    var month = (now.getMonth() + 1);
    var day = now.getDate();
    if(month < 10)
        month = "0" + month;
    if(day < 10)
        day = "0" + day;
    var birthday = now.getFullYear() + '-' + month + '-' + day;

    entry.set('user_birthday', birthday);
    view = new App.Views.EditEntry({'model' : entry});

    // view = $('#edit-entry-modal').html(this.template({'entry' : entry}));
    var modal = new Backbone.BootstrapModal({ content: view, title: 'Edit', okText: 'Save'}).open();
    modal.on('ok', function() {
      //Do some validation etc.
      var data = {};
      modal.$el.find('form').serializeArray().map(function(x){data[x.name] = x.value;});
      entry.save(data,{
         succes: function(entry, respone){
           console.log(entry);
         },
         error: function(entry, response){
            that.handleError(entry, response);
         }
      });
    });
  },
  handleError: function(entry, response){
    if (response.status == 422)
      {
       var errors = $.parseJSON(response.responseText).errors;
       _.each(errors, function(error, attribute){
          $(".alert .message").text(error);
          $(".alert").show();
       });
      }
  }
});
