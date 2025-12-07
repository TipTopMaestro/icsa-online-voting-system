# ICSA ONLINE VOTING SYSTEM
## PROJECT MANUSCRIPT

---

## PROJECT DESCRIPTION

The ICSA Online Voting System is a web-based application designed for the Institute of Computing Student Association (ICSA) at Davao del Norte State College. This system allows students to vote for their organization officers through the internet instead of using the traditional Google Forms method. The project was developed over a 2-month period as part of our second-year coursework, covering three subjects: IT ELECT2 (Frontend Development), IT ELECT3 (Backend Development), and IM (Database Management).

The system provides a complete voting experience with three types of user accounts: Admin, Voter, and Candidate. Admins can create and manage elections, voters can cast their votes securely, and candidates can view their campaign information and track election results. The platform ensures that each student can only vote once per election and provides real-time results as votes are being counted.

This project addresses the limitations of the previous Google Forms-based voting system by providing better security, automatic vote counting, and a more organized way to manage elections. It also reduces the administrative workload by automating most of the election processes.

---

## OBJECTIVES

### General Objective
To develop a secure and user-friendly online voting system that allows ICSA members to participate in officer elections digitally, replacing the manual Google Forms-based process with an automated platform.

### Specific Objectives

1. **Create a centralized voting platform** where students can cast their votes online without needing paper ballots or manual counting.

2. **Implement role-based access control** to separate the functions of administrators, voters, and candidates, ensuring each user only sees what they need to see.

3. **Ensure vote security and integrity** by preventing double voting, protecting voter privacy, and maintaining accurate vote counts.

4. **Provide real-time election monitoring** that shows vote counts, turnout rates, and election statistics as voting happens.

5. **Automate election management tasks** such as voter registration verification, candidate account creation, and result tabulation to reduce human error.

6. **Generate instant election results** with charts and graphs that can be viewed immediately after the election ends without manual counting.

7. **Enable better candidate representation** by allowing candidates to upload their photos, platforms, and campaign information that voters can review before voting.

8. **Maintain a record of past elections** so administrators and students can look back at previous results and participation rates.

---

## SCOPE AND LIMITATIONS

### Scope

**What the System Covers:**
- Serves 100-500 BSIT and BSIS students of ICSA at DNSC
- Handles ICSA officer elections only
- Works on desktop, tablet, and mobile devices
- Built with Laravel 12, Vue.js 3, and MySQL

**Admin Can:**
- Create and manage elections
- Add positions and candidates
- Send login credentials to candidates via email
- Monitor voting progress and view results in real-time
- Publish announcements

**Voters Can:**
- Register using approved student ID
- View candidate profiles and platforms
- Cast votes for multiple positions
- Get voting receipt after voting
- View election results

**Candidates Can:**
- Upload profile photo and campaign platform
- Monitor their vote count
- View announcements and results

### Limitations

1. Requires stable internet connection to access the system
2. Only one election can be active at a time
3. Votes cannot be changed after submission
4. Only pre-approved BSIT/BSIS students can register
5. Admins must manually add student IDs to approved list
6. Voters must vote for at least one candidate per position
7. System only supports officer elections (no surveys or polls)

---

## FUNCTIONALITIES

### 1. Authentication and Registration
- Students register with name, email, password, and student ID
- System validates if student ID is in approved list (BSIT/BSIS only)
- Three user roles: Admin, Voter, and Candidate
- Login with email and password
- Password recovery and two-factor authentication available

### 2. Admin Features
- Create and manage elections (title, description, start/end dates)
- Add positions for each election (President, Vice President, etc.)
- Register candidates and auto-generate login credentials
- Send candidate credentials via email
- Monitor real-time voting progress and turnout
- View election results with charts
- Create and publish announcements
- View list of all registered voters

### 3. Voter Features
- View all candidates with photos and platforms
- Cast votes for multiple positions in one ballot
- Receive voting receipt with timestamp
- View real-time election results
- Update profile and upload photo
- Read announcements from admin
- Dashboard shows voting status and election countdown

### 4. Candidate Features
- Upload profile photo and campaign platform
- Monitor personal vote count during election
- View election results and announcements
- Update profile information and password

### 5. Security and System Features
- One vote per student per election (prevents double voting)
- Password encryption using bcrypt
- Role-based access control
- SQL injection and XSS protection
- Real-time vote count updates without page refresh
- Responsive design for desktop, tablet, and mobile
- Photo upload with validation

---

## NON-FUNCTIONALITIES

### 1. Performance
- Pages load within 2-3 seconds on average internet speed
- Vote submission processes in under 1 second
- Supports 100-500 users voting at the same time
- Real-time updates appear within 2 seconds

### 2. Usability
- Simple and clean interface that students can easily understand
- New users can register and vote within 5 minutes
- Clear error messages when something goes wrong
- Works on different screen sizes (desktop, tablet, mobile)

### 3. Reliability
- System available 24/7 during election periods
- Vote counts remain accurate throughout the election
- No vote duplication or loss during submission
- Automatic error logging for troubleshooting

### 4. Security
- Passwords encrypted using bcrypt hashing
- Protection against SQL injection and XSS attacks
- Role-based access (Admin/Voter/Candidate cannot access each other's functions)
- Vote privacy maintained (admin cannot see who voted for whom)

### 5. Maintainability
- Follows MVC architecture (organized code structure)
- Clear folder structure for easy navigation
- Commented code for complex functions
- Uses Git version control for tracking changes

### 6. Compatibility
- Works on Chrome, Firefox, Edge, and Safari browsers
- Compatible with Windows, Mac, and Linux computers
- Responsive on tablets and smartphones
- Requires PHP 8.2+, MySQL 5.7+, and Node.js 18+

---

## CONCLUSION

The ICSA Online Voting System successfully provides a modern solution for conducting student organization elections at DNSC. By moving from Google Forms to a dedicated platform, the system offers better security, automated vote counting, and improved user experience. The project demonstrates the practical application of web development skills learned in IT ELECT2 (Frontend), IT ELECT3 (Backend), and IM (Database) subjects.

Through this system, ICSA members can now participate in elections more conveniently, administrators can manage elections more efficiently, and candidates can engage better with voters through their campaign platforms. The real-time results and analytics features provide immediate transparency, while the security measures ensure fair and accurate elections.

---

**Prepared by:**
- Froyd Carbajosa
- Kimbie Batilong
- Liezel Tumagan
- Felaura Vivien Golosino

**Institute of Computing**  
**Davao del Norte State College**  
**Academic Year 2024-2025**
