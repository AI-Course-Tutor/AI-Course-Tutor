# AI-Course-Tutor
## Description
A PHP-based frontend for using ChatGPT in the context of teaching courses. The tutor is designed to provide students with feedback on stored assignments. The setup is intended to be kept as simple as possible (no use of Assistants or similar).

## Installation

### Deployment to the Internet

If you want to deploy it to the internet, you require a web server with:
- PHP
- MySQL database

### Local Testing

With the following step-by-step instruction we want to enable you to setup, run and try-out this project on your local machine without requiring a web server that is accessible of the internet. Note that there their are multiple web servers that can be used locally, such that the following procedure should just be considered one example of how local testing could be achieved.

#### 1. Download and Unpack "Uniform Server"

- Go to [Uniform Server / MiniServer on SourceForge](https://sourceforge.net/projects/miniserver/)
- Download the latest version: We tested it with `15_0_2_ZeroXV.exe`
- Run the `.exe` file to unpack the server files to a folder of your choice

#### 2. Prepare the Web Server

- Go into the `www` folder inside the `UniServerZ` directory
- Delete all files
- Clone or copy this project into a folder (e.g. `AI-Course-Tutor`) inside the `www` folder

#### 3. Start the Server Environment

- Navigate to the `UniServerZ` folder and run `UniController.exe`
- When prompted by the Windows Firewall whether to allow network access or to cancel (appears multiple times), you can also cancel and it should still work because it runs locally and does not require network access
- Set a MySQL **root password** of your choice (you won’t need it again)
- In the UniController GUI:
    - Go to **Apache** → **Change Apache Root-folders** → **Select new Server-root Folder (www)**, then select the `public` folder inside your project (e.g. `AI-Course-Tutor/public`)
    - Click **Start Apache**
    - Click **Start MySQL**
    - Click **phpMyAdmin** (your browser will open)

#### 4. Create the Database and User

In **phpMyAdmin**:

1. Go to **"User accounts"** → **"Add user account"**
    - **User name**: `aicoursetutor`
    - **Host name**: change dropdown to `Local` (this will result in `localhost` appearing in the input field)
    - **Password**: `aicoursetutor_password` (enter this into password and re-type text fields)
    - Enable checkbox: **"Create database with same name and grant all privileges."**
    - Click **Go**
2. In the left sidebar, click the newly created database `aicoursetutor`
3. Go to the **SQL** tab
    - Copy the SQL command from [src/Database.php](src/Database.php)
    - Paste it into the text field and click **Go**

#### 5. Configure Environment Variables

- Inside the project folder (e.g. `AI-Course-Tutor`), copy `.env.example` to `.env`
- Edit the `.env` file with the following values:
```
DATABASE_HOST="localhost"
DATABASE_DB_NAME="aicoursetutor"
DATABASE_USERNAME="aicoursetutor"
DATABASE_PASSWORD="aicoursetutor_password"

OPENAI_API_KEY="REPLACE_WITH_OPENAI_API_KEY"
```

> ⚠️ You must obtain an API key from [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys) ("Create new secret key" -> "Service Account") and replace `REPLACE_WITH_OPENAI_API_KEY` with your personal API key that is used to authorize access to the GPT models and perform the billing.

#### 6. Install Composer Dependencies

- Open the Windows Command Prompt
- Navigate to the project folder inside `www`, e.g. `www/AI-Course-Tutor/`
```
For example, if you installation is located in c:\UniServerZ, use the following cd (change directory) command:
cd c:\UniServerZ\www\AI-Course-Tutor
``` 
- Then, run the following command (assuming UniServer’s PHP files are located two levels up):
```
..\..\core\php83\php.exe composer.phar install
```

This will install all required dependencies into the `vendor` folder.

#### 7. Open the Tutor

- You should now be able to access the tutor by entering the following URL into your browser: [http://localhost/](http://localhost/)

### 8. Troubleshooting

- If you see no website or an error message occurs, do as follows to access the Apache (= web server) error log files:
    - In the UniController GUI:
        - Go to **Apache** → **Apache Logs** → **View Apache Error Log** to inspect errors (Note: latest errors are at bottom of the file)
- If you use UniServerZ and the website feels rather slow when navigating from page to page:
    - edit file `core/mysql/my.ini`:
        - replace `bind-address=127.0.0.1` with `bind-address=::`
        - restart Apache and MySQL
        - test whether page loads faster now

## Configuration
The project uses a configuration-based system similar to the `.env.example`/`.env` pattern. This allows easy customization without modifying core files and maintains clean git history when forking the project.

### Configuration Pattern
The system uses **default configuration files** (`.example` files) that are checked into git, and **system-specific configuration files** that are ignored by git:

- **Default files** (checked into git):
  - `config/placeholders.example.json` - Template for placeholder configuration
  - `config/system.example.json` - Template for system settings
  - `config/tutor-modes.example.json` - Template for tutor modes

- **System-specific files** (ignored by git):
  - `config/placeholders.json` - Your customized placeholder configuration
  - `config/system.json` - Your customized system settings  
  - `config/tutor-modes.json` - Your customized tutor modes

### Setup Instructions
1. **Copy the example files** to create your system-specific configurations:
   ```bash
   copy config\placeholders.example.json config\placeholders.json
   copy config\system.example.json config\system.json
   copy config\tutor-modes.example.json config\tutor-modes.json
   ```

2. **Edit your system-specific files** with your custom values

3. **The system automatically falls back** to `.example` files if system-specific files don't exist

### Placeholder Configuration (`config/placeholders.json`)
Copy from `placeholders.example.json` and customize (e.g., contact information or tutor name used in textual output across project).

### System Configuration (`config/system.json`)
Copy from `system.example.json` and customize:
- `gpt.model`: OpenAI model to use
- `gpt.max_tokens`: Maximum tokens per response
- `consent.enabled`: Enable/disable consent system
  - `consent.options`: Map of consent options. Each option can define:
    - `consent_text` (supports placeholders)
    - `tutor_access`
    - `pretest_required_before_tutor_access`
    - `pretest_url` (URL used when pretest is required)
- `access_token.enabled`: Enable/disable access token gating
  - `access_token.tokens`: Array of tokens with:
    - `value`: the token string
    - optional `valid_from` and/or `valid_to` in `YYYY-MM-DD`
- `sidebar.enabled`: Show/hide the sidebar
  - `sidebar.show_logout_button`
  - `sidebar.show_start_new_conversation_button`
  - `sidebar.show_conversation_history`
- `authentication.post.enabled`: Allow POST (form) authentication
  - `authentication.post.require_password` (true: login with username and password; false: login with username only)
  - `authentication.post.password_validation`: controls minimum length and character requirements
  - `authentication.post.username_validation`: controls the allowed username format (e.g., only letters and numbers)
- `authentication.get.enabled`: Allow GET (URL) authentication
  - `authentication.get.require_token` (true: login with username and token; false: login with username only)
  - `authentication.get.token_validation`: controls minimum length and allowed characters
  - `authentication.get.username_validation`: controls the allowed username format (e.g., only letters and numbers)

### Tutor Modes Configuration (`config/tutor-modes.json`)
Copy from `tutor-modes.example.json` and customize:
- Enable/disable modes (e.g., a general questions mode)
- Configure either `simple_button` modes or `homework_sections` with nested `sections` and `tasks`
- Set availability windows (available_from/available_to) at mode or section level
- Configure button texts and `tutor_mode_value` strings (format: `folder$task#Conversation Title`)

For detailed instructions on setting up prompts for the tutor modes, see also section [Tutor Modes / Prompts](#tutor-modes--prompts) below.

### CSS Color Customization
You can customize the color scheme of the Tutor by creating a custom CSS file:

1. **Create a custom colors file**: In the `public/assets/` directory, create a file named `colors.custom.css`

2. **Override color variables**: The file should use the same CSS custom property structure as `colors.css`. For example:
   ```css
   :root {
       /* Override primary colors */
       --primary-color: rgb(50, 100, 150);
       --secondary-color: rgb(255, 140, 0);
   }
   ```

3. **Automatic inclusion**: If `colors.custom.css` exists, it will be automatically included in the header after the default `colors.css`, allowing your custom colors to override the defaults.

4. **Available variables**: See `public/assets/colors.css` for all available CSS custom properties that can be overridden.

This approach allows you to customize colors without modifying the core CSS files, making it easy to maintain your customizations when updating the project.


### Template Content Customization
There are also some template files that regularly require customization, such as legal notice or privacy policy. These files also follow the .example pattern described above. That is, copy the respective files to the same filename without example in its name and customize (e.g., copy `templates/pages/privacy-policy.example.php` to `templates/pages/privacy-policy.php` and customize the content).


### Database
The following parameters must be changed in `.env` (copy and rename the `.env.example` for this):

| Variable | Description |
| --- | --- |
|`DATABASE_HOST`| Enter the IP of the SQL database server here. If it runs on your own computer, `localhost` is sufficient |
|`DATABASE_DB_NAME`| Name of the database to be used |
|`DATABASE_USERNAME`| user name to use for database access |
|`DATABASE_PASSWORD`| user password |

### ChatGPT
To establish a connection with ChatGPT, an API key must be stored in the `.env` file. The API key is generated at [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys) ("Create new secret key" -> "Service Account") and then copied here. The key must not appear anywhere else! Requests to ChatGPT are authorized through it and costs are billed. If this key falls into the wrong hands, it can become expensive!

| Variable | Description |
| --- | --- |
|`OPENAI_API_KEY`| Key in the form of `sk-...` |

The model to be used is configured in `config/system.json` under `gpt.model`. Both the performance of the tutor and the costs depend on the model. See https://platform.openai.com/docs/pricing for available models and pricing.

## Tutor Modes / Prompts
The prompts that control the behavior of the tutor are stored in text files stored in the "tutor_modes" folder. (Note: Make sure to add them as UTF-8 encoded text files)

Whenever the student starts a new chat, the prompts are added as starting messages to the chat, thereby controlling the tutor's behavior and providing the tutor with the background information that might be necessary for the present chat (e.g. task solution that the tutor should guide the student toward).

The prompts can be organized in several "layers", such as:
1. Main prompts / Default prompts: All prompts in the `tutor-modes/!default` folder are added before any other prompts. It can be used to define the general role of the tutor and limitations of the answers that the model is allowed to give.
2. Tutor mode-specific / Task-specific prompts: Additional prompts for the respective mode or task are added, e.g. to determine general behavior for a homework task or to provide the tutor with information on the task solution.

As an example, we included the default prompts of our R-Tutor, as well as the general questions mode and two homework specific mode. These can be used as inspiration to adapt the prompts to your own needs.

Note: In addition to the file-based prompts described here, there are also system prompts for the solution button toggle functionality that are configured in `config/tutor-modes.json` under `solution_button`. In order to decide which solution button configuration to use for a chat, the `tutor_mode_value` of a chat is tested whether it starts with `tutor_mode_value_starts_with`, and then the respective configuration is used (enabled: true, show solution button, or false, do not show solution button; if enabled the respective system prompts are used whenever the solution button toggle state changes with a new user message).

### Organization of Prompts
The prompts are defined in the [tutor-modes](tutor-modes) folder. A folder is created for each session or tutor mode. Within this folder, another folder is created for each task. Within the task folder, there can be multiple prompt files that are *always* named as `[order_of_execution]_[assistant/system].txt`, such as `1_assistant.txt`, `2_system.txt`, and `3_system.txt`

```
tutor-modes
    |- !default [optional]
    |   |- 1_system.txt    
    |- session-1
    |   |- task-1
    |   |   |- 1_assistant.txt
    |   |   |- 2_system.txt
    |   |   |- 3_system.txt
    |   |- task-2
    |   |   |- ...
    |   |- ...
    |- session-2
    |   |- ...
    |- ...
```
*Fig1.: Folder structure in tutor-modes*

The second part in the prompt files defines whether the content of the respective file should be added as system prompt or assistant prompt:
- system prompt: Content is hidden from the user and can be used to control the tutor or to provide the tutor with hidden information, such as task solution.
- assistant prompt: Visible in the chat as assistant answer. Could be used to provide an initial message of the tutor to the student for the respective task, such as providing the task to the student. 

### Mode Selection Page
The mode selection page is rendered from `templates/pages/select.php` and is fully driven by `config/tutor-modes.json`. You do NOT need to edit the template to add or remove modes. Buttons, sections, and their availability are generated dynamically from the JSON configuration.

Note: The part after `#` in a `tutor_mode_value` is used as the conversation title in the history, e.g., `data-preparation-2$task-2#Data Prep 2: Task 2` results in the title `Data Prep 2: Task 2` in the converstation history.

## Screenshots

### Select tutor mode (general question vs. compare homework task)

Layout defined via [config/tutor-modes.json](config/tutor-modes.example.json)

<img width="580" height="681" alt="screenshot_select_tutor_mode" src="https://github.com/user-attachments/assets/12e114af-3857-48b3-bfb2-28281b484f71" />

### Asking a general question

Uses the following prompts:
1. system prompt: [tutor-modes/!default/1_system.txt](tutor-modes/!default/1_system.txt)
2. assistant prompt: [tutor-modes/general/question/1_assistant.txt](tutor-modes/general/question/1_assistant.txt)

<img width="580" height="695" alt="screenshot_general_questions" src="https://github.com/user-attachments/assets/1bf5b718-8171-4896-aca0-8e229e8c1e89" />

### Compare homework task (guide toward solution)

Uses the following prompts:
1. system prompt: [tutor-modes/!default/1_system.txt](tutor-modes/!default/1_system.txt)
2. assistant prompt: [tutor-modes/homework-plotting-2/task-2/1_assistant.txt](tutor-modes/homework-plotting-2/task-2/1_assistant.txt)
3. system prompt: [tutor-modes/homework-plotting-2/task-2/2_system.txt](tutor-modes/homework-plotting-2/task-2/2_system.txt)
4. system prompt: [tutor-modes/homework-plotting-2/task-2/3_system.txt](tutor-modes/homework-plotting-2/task-2/3_system.txt)

<img width="580" height="695" alt="screenshot_compare_homework_task" src="https://github.com/user-attachments/assets/88bcce3d-2e69-4ffa-9b89-7f1fd766e036" />


## Contributors / Contact

### Contributors

- Dr. Frank Papenmeier

### Contact

For questions regarding this project, please contact Dr. Frank Papenmeier at frank.papenmeier@uni-tuebingen.de
