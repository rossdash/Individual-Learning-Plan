Plugin for adding an Individual Learning Plan to Mahara

Many National Vocational Qualifications (NVQ) involve the formulation of ILPs to record and keep track of a student's progress throughout the length of the course. These courses use a point system, the course has a set point value that needs to be achieved by the students. This is achieved by the selection of units, that have different point values depending on the level of the NVQ. 

This Mahara plugin allows simple ILPs to be maintained. It includes fields for setting the title, description, status, point value, target completion date and the actual date of completion of individual units.  It also does calculations on the points,  displaying the remaining points needed, the acquired points and the total value of the units added. Each ILP is set up to include the title of the course, its description and it's set point value.

The ILP can be added to a Mahara view which can also include related resources and artefacts e.g. a blog, piece of text or google document that illustrates the work done on the units included in the ILP.

The plugin allows a user to have multiple instances of ILPs. So, a user can store ILPs for multiple NVQs they may partake.

The plugin adds a new 'ILP' tab (similar to 'My Plans') to 'My Portfolio' in Mahara. The code was based on 'My Plans' 


Installation

The plugin is currently available from GitHub.

git://github.com/rossdash/Individual-Learning-Plan.git

Download, unpack and copy the /ilps folder (and included files and folders) into the /artefact folder of your Mahara installation. Then enable the ilps and units from the Plugin Administration interface.

The plugin should work with any Mahara 1.4.x installation . It creates a new artefact_ilps_unit table and a artefact_ilps_points table which have a 1:1 extension of the artefact table. Please test it on a development installation before production deployment.

I created this as part of my dissertation piece at Bournemouth University for my BSc (Hons) Software Engineering course. The customer for the dissertation is Yeovil College's IT Professional NVQ Level 3 Assessor. The dissertation is still ongoing.

I hope it may be useful,

Ross