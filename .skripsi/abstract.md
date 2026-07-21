# ABSTRACT

*Inventory management is a critical aspect of cafe operations that affects raw material availability, production continuity, and customer satisfaction. In practice, many cafes still record stock manually using paper notes or spreadsheets, which are prone to recording errors, delayed stock information, and difficulty in tracking stock movement history. This study aims to design and implement an inventory management system for the W9 Cafe Point of Sale using Laravel and Filament that can manage recipe-based raw material stock.*

*The system was developed using the Laravel 13 framework with the Filament administration panel, which provides intuitive data management interfaces and batch management. The system implements two batch deduction modes: FEFO (First-Expiry-First-Out) and FIFO (First-In-First-Out), with data locking mechanisms to prevent conflicts in concurrent transactions. Integration with the cashier transaction module is achieved automatically where stock deduction occurs as part of order processing.*

*Testing was conducted using black box and white box methods. Black box testing across all modules showed scenarios passed. White box testing validated FIFO and FEFO algorithms. All stock deduction mechanisms and movement recording functioned as designed.*

*Keywords: Inventory management system, Point of Sale, Laravel, Filament, FEFO, FIFO.*
