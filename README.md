# IIT Delhi Public User Pages

## What?

IIT Delhi has a public web-hosting facility for students. But sadly it is limited to PhD students only. (See [CSC website](http://www.cc.iitd.ernet.in/CSC/index.php?option=com_content&view=article&id=107&Itemid=146))
Some departments (like CS) have their own system for allowing undergrads to host their public web-pages. But none exists currently for EE and other department students. So here is a simple script that can be used by other departments (like EE) to provide similar facility to their students.

## How it works?

This script uses the existing `privateweb.iitd.ac.in` (internal) server that is accessible to all students. All requests are routed to the internal server, and responses are relayed back. It has the benefit that user pages need not be stored on the department (EES) web-server & the (php) scripts are not run in the context of department web-server.

## Usage

### Department Server Admin

To setup the facility, simply copy the [archive](https://github.com/coderkd10/IITD_User_Pages/releases/download/v1.0.0/release.tar.gz) from releases section and paste them on the web-server root (taking appropriate care of conflicts). User pages are then available as `http://ees.iitd.ac.in/user.php/<user-id>`.

### Students

- Log on to your CSC accounts via `ssh1` or otherwise.
- `cd` into `private_html` directory in your home folder.
- Create a new directory `ees_home` inside `private_html`
- Copy your web pages / php scripts to `ees_home` directory. 
- Make sure you have correct permissions set (check if your site is accessible at `http://privateweb.iitd.ac.in/~<user-id>/ees_home` from inside IITD).

Now your site should now be accessible publicly!

## Demo

For demo I have deployed this script to my account @ `privateweb.iitd.ac.in/~ee1130431/user.php`.  
**Original Page** : http://privateweb.iitd.ac.in/~ee1130431/ees_home/  
**Proxied Page** : http://privateweb.iitd.ac.in/~ee1130431/user.php/ee1130431/ (Since the script is not deployed on web-server that is accessible from outside IITD so this page is also internal for now.)
