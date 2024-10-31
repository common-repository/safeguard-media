var wpsm_elementor = null;
(function($) {
  wpsm_elementor = {
    active_panel: null,

    init: function() {
      let that = this;

      WPSM_Popup_Uploader.send_editor = that.send_editor;
      WPSM_Popup_Uploader.getPostID = that.getPostID;

      if(elementor) {
        elementor.hooks.addAction('panel/open_editor/widget/wpsm_widget', function(panel, model, view) {
          that.active_panel = panel;

          let element = panel.$el.find('.elementor-button[data-event="safeguardmedia:editor:modal"]');

          if(element.length) {
            element.on('click', that.showModal);
          }
        });
      }
    },
    showModal: function() {
      WPSM_Popup_Uploader.dialog.dialog("open");
    },

    send_editor : function(name) {
      let that = wpsm_elementor;
      let post_id = wpsm_safeguard_uploader_data.ID;

      wpsafeguard_process_setting('sendeditor', 'start');

      let request = {
        action: 'wpsm_get_file_settings',
        filename: name,
        post_id: post_id,
        type: 'json',
        nonce: wpsm_safeguard_uploader_data.nonce
      };

      $.post(ajaxurl, request, function (response) {
        if(response.parameters) {
          that.active_panel.$el.find('.elementor-control-wpsm_name input').val(name).trigger('input');
          that.active_panel.$el.find('.elementor-control-wpsm_width input').val(response.parameters.width).trigger('input');
          that.active_panel.$el.find('.elementor-control-wpsm_height input').val(response.parameters.height).trigger('input');
          that.active_panel.$el.find('.elementor-control-wpsm_remote select').val(response.parameters.remote).trigger('change');
        }

        WPSM_Popup_Uploader.dialog.dialog( "close" );
        wpsafeguard_process_setting('sendeditor', 'end');
      });
    },

    saveSettings : function(obj) {
      let that = this;
      let postid   = that.getPostID();
      let setData  = {};
      let filename = $(obj).attr('data-filename');

      $("#wpsafeguard_setting_body input").each(function () {
        var nname = $(this).attr("name");
        if (nname == "print_anywhere" || nname == "allow_capture" || nname == "allow_remote") {
          setData[nname] = ($(this).attr("checked") == "checked") ? "checked" : "";
        } else {
          setData[nname] = $(this).val();
        }
      });

      let request = {
        action: 'wpsm_save_file_settings',
        post_id: postid,
        nname: filename,
        set_data: setData,
        nonce: wpsm_safeguard_uploader_data.nonce
      };

      wpsafeguard_process_setting("setting", "start");

      $.post(ajaxurl, request, function (param) {
        $("#wpsafeguard_message").html(param.message);
        wpsafeguard_process_setting("setting", "end");
        that.send_editor(filename);
        that.clear();
        WPSM_Popup_Uploader.load_files();
      });
    },

    getPostID: function() {
      return wpsm_safeguard_uploader_data.ID;
    }
  };

  $(document).ready(function() {
    wpsm_elementor.init();
  })
})(jQuery);