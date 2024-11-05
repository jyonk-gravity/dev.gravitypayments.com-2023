function gfFormsAddConditionalColumns(version) {
  /*
    The callback function will accept a list of elements, and will cycle
    through identifying and acting on those elements that have the gfmc-column
    class associated to them.
    When that class is found the objective is to detect if the display: none has
    been associated to the elements that it contains, and if all if this is
    the case, then apply display: none to the parent gfmc-column class
    element.
  */
  let config = {
    attributes: true,
    attributeFilter: ['style'],
    childList: false,
    subtree: true,
  };
  
  function callback(mutationList) {
    console.log ('callback');
    mutationList.forEach(function(mutation) {
      // Check if the modified element has display set to none
      // NOTE, this may need to be modified based on the condition that an
      // element goes from none to another status.  Perhaps it is better to
      // identify changes in elements underneath the className gfmc-column
      // and assume that the change is a toggle to display.
      if (mutation.type === 'attributes') {
        if (mutation.target.parentElement.parentElement.className.search(
            'gfmc-column') !== -1) {
          CheckParentsChildrenDisplayValueAndHideParentIfNecessary(
              mutation.target.parentElement.parentElement,
              mutation.target.style.display);
        }
      }
    });
  }
  
  function callback25(mutationList) {
    console.log ('callback25');
    mutationList.forEach(function(mutation) {
      // Check if the modified element has display set to none
      // NOTE, this may need to be modified based on the condition that an
      // element goes from none to another status.  Perhaps it is better to
      // identify changes in elements underneath the className gfmc-column
      // and assume that the change is a toggle to display.
      if (mutation.type === 'attributes') {
        var gfmcParentElement = mutation.target.parentElement;
        var gfmcColumnWrapper = gfmcParentElement.parentElement;
        
        if (gfmcParentElement.className.search(
            'gfmc-column') !== -1) {
          CheckParentsChildrenDisplayValueAndHideParentIfNecessary(
              gfmcColumnWrapper, mutation.target.style.display);
        }
      }
    });
  }
  
  function CheckParentsChildrenDisplayValueAndHideParentIfNecessary(
      GFMCParent, targetDisplayValue) {
    if (GFMCParent.firstChild.children.length === 1) {
      targetDisplayValue !== 'none' ?
          ShowParent(GFMCParent) : HideParent(GFMCParent);
    } else if (GFMCParent.firstChild.children.length > 1) {
      if (AreAllChildrenHidden(GFMCParent)) {
        HideParent(GFMCParent);
      } else {
        ShowParent(GFMCParent);
      }
    }
  }
  
  function HideParent(node) {
    node.style.display = 'none';
  }
  
  function ShowParent(node) {
    node.style.removeProperty('display');
  }
  
  function AreAllChildrenHidden(node) {
    let childrenHiddenFlag = true;
    for (let child of node.firstChild.children) {
      if (child.style !== 'undefined') {
        child.style.display !== 'none' ? childrenHiddenFlag = false : null;
      }
    }
    return childrenHiddenFlag;
  }
  
  // Base the application of observing the form elements on a page by page basis
  // Determine if pages are present in the form.
  let gravityFormPages = document.getElementsByClassName('gform_page');
  let observer = (version >= 2.5) ? new MutationObserver(callback25) : new MutationObserver(callback);
  
  if (gravityFormPages.length > 0) {
    for (let i = 0; i < gravityFormPages.length; i++) {
      observer.observe(gravityFormPages[i], config);
    }
  } else {
    let gravityForm = document.querySelector('.gform_body');
    observer.observe(gravityForm, config);
  }
}
