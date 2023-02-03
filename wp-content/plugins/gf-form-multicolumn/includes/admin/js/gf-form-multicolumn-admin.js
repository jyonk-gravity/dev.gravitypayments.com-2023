function gfmc_validate_form_columns (error, form, has_product, has_option) {
  var result = check_start_and_end_quantities(form)
  if (result !== 0) {
    if (result > 0) {
      return result + gfmc_scripts_admin_strings.tooManyColumnStarts
    } else {
      return -result + gfmc_scripts_admin_strings.tooManyColumnEnds
    }
  }
}

function check_start_and_end_quantities (form) {
  var columnStartCounter = 0
  var columnEndCounter = 0
  jQuery.each(form.fields, (field, value) => {
    if (value.type === 'column_start') {
      columnStartCounter++
    }
    if (value.type === 'column_end') {
      columnEndCounter++
    }
  })
  return columnStartCounter - columnEndCounter
}
