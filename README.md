# AI-Course-Tutor
## Description
A PHP-based frontend for using chatGPT in the context of teaching courses. The tutor is designed to provide students with feedback on stored assignments. The setup is intended to be kept as simple as possible (no use of Assistants or similar).

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

## Necessary Adaptations
The project should be adapted to your own needs before use. To facilitate the adaptation, we have defined the following placeholders that can be replaced throughout the project:
| Placeholder | Description |
| --- | --- |
|[your-name]| Name of the project responsible person (prp) |
|[your-email]| Email of the prp |
|[your-address]| Address of the prp |
|[your-phone]| Phone number of the prp |
|[your-department]| Department of the prp |
|[your-institution]| Institution of the prp |
|[additional-name]| Name of an additional person |
|[additional-email]| Email of an additional person |
|[your-study-title]| Study title |
|[your-tutor-name]| Name of the tutor |
|[your-study-description]| Study description |
|[your-consent-text]| Consent text |
|[your-privacy-policy-url]| URL to the institution's privacy policy |
|[your-impressum-url]| URL to the institution's imprint|

### Admin Contact
A contact name and contact email are presented in several error messages. Please adjust the following lines of code:
- [src/AccessToken.php](src/AccessToken.php)
- [src/GPT.php](src/GPT.php)
- [templates/footer.php](templates/footer.php)
- [public/consent.php](public/consent.php)

### Imprint / Impressum
For legal reasons, the imprint must be adapted to the responsible institution.
- [Imprint](templates/impressum.php)

### Privacy Policy
If data is collected, it should be mentioned here!
Also mention the use of and thus transmission of data to an American company.
- [Privacy Policy](templates/datenschutzerklaerung.php)

--> if you require a consent form, activate it (e.g., at the top of [public/index.php](public/index.php)) 

### Database
The following parameters must be changed in `.env` (copy and rename the `.env.example` for this):
| Variable | Description |
| --- | --- |
|`DATABASE_HOST`| Enter the IP of the SQL database server here. If it runs on your own computer, `localhost` is sufficient |
|`DATABASE_DB_NAME`| Name of the database to be used |
|`DATABASE_USERNAME`| user name to use for database access |
|`DATABASE_PASSWORD`| user password |

### chatGPT
To establish a connection with chatGPT, an API key must be stored in the `.env` file. The API key is generated at [https://platform.openai.com/api-keys](https://platform.openai.com/api-keys) ("Create new secret key" -> "Service Account") and then copied here. The key must not appear anywhere else! Requests to chatGPT are authorized through it and costs are billed. If this key falls into the wrong hands, it can become expensive!
| Variable | Description |
| --- | --- |
|`OPENAI_API_KEY`| Key in the form of `sk-...` |

The model to be used is entered in [src/GPT.php](src/GPT.php). Both the performance of the tutor and the costs depend on the model. See [https://platform.openai.com/docs/pricing](https://platform.openai.com/docs/pricing) for model names that can be used and their pricing (so far we tested gpt-... models). 

## Tutor Modes / Prompts
The prompts that control the behavior of the tutor are stored in text files stored in the "tutor_modes" folder. (Note: Make sure to add them as UTF-8 encoded text files)

Whenever the student starts a new chat, the prompts are added as starting messages to the chat, thereby controlling the tutor's behavior and providing the tutor with the background information that might be necessary for the present chat (e.g. task solution that the tutor should guide the student toward).

The prompts can be organized in several "layers", such as:
1. Main prompts / Default prompts: All prompts in the `tutor-modes/!default` folder are added before any other prompts. It can be used to define the general role of the tutor and limitations of the answers that the model is allowed to give.
2. Tutor mode-specific / Task-specific prompts: Additional prompts for the respective mode or task are added, e.g. to determine general behavior for a homework task or to provide the tutor with information on the task solution.

As an example, we included the default prompts of our R-Tutor, as well as the general questions mode and one homework specific mode. These can be used as inspiration to adapt the prompts to your own needs.

Note: There is currently the following exception to the rule that all prompts are stored in text files:
The file [public/chat.php](public/chat.php) contains the system prompts for the solution toggle button, that is it will enter a new system prompt into the chat history whenever the toggle button changes. These prompts are still specific to R and should probably be replaced with something fitting for your content. In future development we will probably move them also into text files so they can be easily adapted.
 

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

### Adding the Tutor Modes to the Mode Selection Page
The sessions and tasks created in this way must be made available on the main page of the tutor. For this purpose, the following code block must be inserted in [templates/select.php](templates/select.php) for each session:
```php
<?php if ((new DateTime()) > (new DateTime('[date when the session should appear]')) || current_user_sees_all_boxes()): # date when this should appear on the website ?>
    <div class="selection-box-homework">
        <div>[title of the session]</div>
        <button type="submit" name="tutor_mode" value="[name of the session folder]$[name of the task folder]#[custom label for data collection]">[button label]</button>
        # examples:
        <button type="submit" name="tutor_mode" value="data-preparation-2$task-2#Data Prep 2: Task 2">Task 2</button>
        <button type="submit" name="tutor_mode" value="data-preparation-2$task-3#Data Prep 2: Task 3">Task 3</button>
        #... further buttons
    </div>
<?php endif; ?>
```
Note: The value behind the # is used as title in the conversation history, e.g. `data-preparation-2$task-2#Data Prep 2: Task 2` -> Title is `Data Prep 2: Task 2`


## Contributors / Contact

### Contributors

- Dr. Frank Papenmeier

### Contact

For questions regarding this project, please contact Dr. Frank Papenmeier at frank.papenmeier@uni-tuebingen.de