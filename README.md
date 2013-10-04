PDFParts module
===============

The PDF Parts module enables a teacher to provide a set of freely selected pages from a PDF file as a course resource.

How to use
----------

1. Add a new PDFParts to a course.
2. Configure all of the standard settings: name, description etc.
3. `Select PDF file` setting - upload a PDF file.
4. `Display` setting - choose the display type (currently, only `Force download` and `Open` are implemented).
5. `Pages` setting - it is similar to the setting from any Print dialog that allows you to select a page or page ranges for printing. The values must be set in a format like 2-4,7,8,10-12.
    
Now when a student clicks on this resource link he gets only the pages specified in `Pages` setting.

Additional features
-------------------

The Teacher can add only one PDFParts instance to a course and set it to dynamically choosing of page ranges.
The module accepts an optional GET parameter called "pages" which will override the corresponding base setting.

Suppose the Teacher added a PDFParts module to course with the id: 4720. Then he can build links in this format: 

```
    http://<MOODLE_SERVER>/mod/pdfparts/view.php?id=4720&pages=2,4
    http://<MOODLE_SERVER>/mod/pdfparts/view.php?id=4720&pages=2-4,7,8,10-12
```

in order to provide the students with the precise pages where the needed information resides, for example the page with the solution to an exercise, or a page with the definition of a term.

Notes
-----

* If the `Pages` setting is empty then the whole document is served to user.
* The `Open` display type may not work in all browser.
* Not all PDF files of version higher than 1.4 are processed without errors. 
 

Credits (acknowledge)
--------------------

The PDFParts module was created based on an idea that came to me after seeing the PDF Page Limiter (mod_pdfpager) module by Geoff Eggins.

The logo is a compilation of two icons from https://openclipart.org : 

1. https://openclipart.org/detail/78169/office-notes-line-drawing-by-sheikh_tuhin
2. https://openclipart.org/detail/171857/icon-pdf---%C3%8Dcone-by-leandrosciola@gmail.com-171857

TODO
----

* The extracted pages are generated on demand; more efficient is to generate once and store the result in the server file cache; then serve to users the file from the cache.
* Add support for PDFtk (http://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/)
