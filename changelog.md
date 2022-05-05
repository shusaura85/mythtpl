### v1.0.2   
-----------
* [fixed] Invalid parameter value passed in compileString() function of Parser\Engine  
* [change] dropped unused parameters from compileTemplate() and compileString() in Parser\Engine  
* [new] initial Unit Test coverage for the code  
  
  
### v1.0.1 PSR-12  
------------------
* [change] changed code formatting style to PSR-12
* [change] changed function names to PSR-12
  
  
### v1.0.0 Vague list of changes from Rain TPL  
------------------------------------------
* [removed] plugin support  
* [removed] blacklist suport  
* [removed] multiple folder support (tpl_dir can no longer be set as array of folders)
* [change] simplified configuration in Tpl class, no longer uses 2 configurations  
* [change] initial template split now uses preg_match_all to split instead of preg_split. Major parsing improvement  
* [change] dropped split regex from tags as they were replaced with a single universal regex that works for all tags (for 49 matched tags on a page, it needed 141k steps, now it uses just 553)  
* [change] parse loop now uses "continue;" to go to next loop instead of using tons of elseif in the code (if's are standalone, and are no longer linked with elseif)  
* [change] constants now accept spaces  
* [change] default config for auto_escape is now false  
* [fixed] array looping without using $ for variables  
* [fixed] constants (or strings) can now handle modifiers properly  
* [fixed] modifier parsing function updated to work with all characters and no longer uses recursion when dealing with multiple modifiers  
* [todo] add suplementary file with commonly used modifier functions that can be used easily in code  
* [new] you can reset assigned variables (using $mythtpl->reset())  
* [new] function $t->p_assign() to add persistent variables (work just like variables) that can survive $mythtpl->reset(). They can be temporarily be overwritten using normal $t->assign(). Remove the overwrite with $t->reset()  
* [new] functions now specify return types and param types   
* [change] $t->assign() (and $t->p_assign()) only accept array of variables  
* [new] function $t->assign_var() to assign a single variable. 3rd parameter indicates if the variable is persistent of not. defaults to not persistent  

