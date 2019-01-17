/* Select2 Field
===================================================================================================================== */

import $ from 'jquery';
import 'select2';

$.entwine('silverware.select2', function($) {
  
  // Handle Select2 Fields:
  
  $('.field.select2field select').entwine({
    
    onmatch: function() {
      
      // Obtain Self:
      
      var $self = $(this);
      
      // Define Result Template:
      
      var templateResult = function(state) {
        return (state.id && state.formattedResult) ? state.formattedResult : state.text;
      };
      
      // Define Selection Template:
      
      var templateSelection = function(state) {
        return (state.id && state.formattedSelection) ? state.formattedSelection : state.text;
      };
      
      // Initialise Select2:
      
      $self.select2({
        templateResult: templateResult,
        templateSelection: templateSelection
      });
      
      // Trigger Next Method:
      
      this._super();
      
    }
    
  });
  
});
