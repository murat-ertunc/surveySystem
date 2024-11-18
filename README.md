# murSurvey (Online Survey System)

**murSurvey**  is a web application that allows you to create online surveys.

***Note: Currently in the development phase (approximately 65% complete, planning end date: 19 November 2024)***

---

## Overview

The application consists of two main projects. The first project (dashboard side) is developed with `Laravel 11.9`, using `TailwindCSS` and `MySQL`. In this part of the system, surveys and users are created and stored. The second project (client side) is developed with `Next.js 15.0.3` and includes `Express.js`, `MySQL`, and `TailwindCSS`. The surveys created in the system are fetched from the MySQL database and rendered. The two projects are connected through a build system. First, you need to build the Next.js project using the npm run build:custom command. This script automatically builds the Next.js project and sends it in a usable format to the Laravel project. After this step, you can run the project.


---
