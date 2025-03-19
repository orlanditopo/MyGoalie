# MyGoalie - Goal Tracking Social Platform

A social platform for tracking and sharing your goals and achievements. Connect with others, share your progress, and celebrate your successes together.

## Features

- User authentication (register/login)
- Create and track personal goals
- Share goals with friends
- Track goal progress (planned, in-progress, completed)
- Friend system for social interaction
- Profile customization
- Responsive design for all devices

## Author

- orlanditopo

## Setup Instructions

1. **Prerequisites**
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Web server (Apache recommended)

2. **Installation**
   - Clone the repository:
     ```bash
     git clone https://github.com/orlanditopo/MyGoalie.git
     ```
   - Copy the project files to your web server directory
   - Create a MySQL database named 'mygoalie'
   - Import the database schema from `database.sql`
   - Copy `src/includes/db.example.php` to `src/includes/db.php` and update the credentials

3. **Configuration**
   - Update database credentials in `src/includes/db.php`
   - Make sure the web server has write permissions for the uploads directory

4. **Running the Application**
   - Start your web server and MySQL
   - Access the application through your web browser
   - Default URL: `http://localhost/MyGoalie`

## Project Structure

```
MyGoalie/
├── src/
│   ├── includes/     # Configuration and utility files
│   ├── pages/        # Main page files
│   ├── actions/      # Form processing and actions
│   ├── auth/         # Authentication files
│   ├── assets/       # CSS, images, and JavaScript
│   └── templates/    # Header and footer templates
├── index.php         # Main entry point
└── database.sql      # Database schema
```

## Development

- The project uses PHP for backend
- MySQL for database
- Vanilla JavaScript for frontend interactivity
- CSS for styling

## Next Steps

1. **Feature Improvements**
   - Add goal categories and tags
   - Implement goal progress tracking with milestones
   - Add notifications for friend activities
   - Create a mobile app version

2. **Technical Improvements**
   - Implement proper error handling and logging
   - Add input validation and sanitization
   - Implement CSRF protection
   - Add unit tests
   - Implement API endpoints for mobile app

3. **UI/UX Improvements**
   - Add dark mode
   - Implement real-time updates
   - Add goal visualization charts
   - Improve mobile responsiveness

4. **Security Enhancements**
   - Implement two-factor authentication
   - Add rate limiting
   - Implement proper session management
   - Add password recovery system

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.


// Original Plans for the site

A website that highlights people's achievements, allow those to be shared with jobs

link that looks like

orlandop.websitename.net

works with GitHub, pictures, scholly, anything that helps you highlight your greatest achievements

Target audience would be people who are in search for jobs or people who would like to track progress

I think of it to be like a trophy room, but instead of only achievements showing the progress as well. You write updates as you go and once you get to the accomplishment it can be like a thread.

Different like (sub) threads for different goals, and

Should this be a social media type or a personal help type?

no goals have to be public/ it can be used as an application to help you track progress but not have to show it to others

Phone app as well because you may want to write something down or track something without wanting to go to a computer/ I think websites like these would not be accessed through safari on iPhone like…

username pw obv, social like adding people

each post has individual link, to make it easy to share, or the sharing would just be like an image of the post and a link to profile 

Timeline? Or "latest achievements" page

I want a quirky name so you can say "damn look at this dudes new (tweet) or goalie"

damn goalkeeper is a good name

"Just posted a goalie" WHAAAAAAAA

No goalkeeper domain names available
Rip

Mygoalie is open

i want people to be able to be able to show for themselves 

people who are wokring for same goals can come to eachother and give tips to eachother with search 

easy remembering, : you forgot what you meal prepped this one month and you can go back to it and find recipe, what and how you cooked it


  As of 5/24
  
  Ive stopped working on the site, but from time to time id like to come back and continue working on it.. 
