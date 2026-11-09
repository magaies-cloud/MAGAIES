import java.util.Properties
import java.io.FileInputStream

plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
    id("com.google.gms.google-services")
}

android {
    namespace = "co.magaiees.app"
    compileSdk = 36
    ndkVersion = flutter.ndkVersion

    compileOptions {
        isCoreLibraryDesugaringEnabled = true
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_17.toString()
    }

    defaultConfig {
        applicationId = "co.magaiees.app"
        minSdk = 24
        targetSdk = 36
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    val keystorePropertiesFile = rootProject.file("key.properties")
    val keystoreProperties = Properties()
    var resolvedKeyFile: java.io.File? = null
    if (keystorePropertiesFile.exists()) {
        keystoreProperties.load(FileInputStream(keystorePropertiesFile))
        val storeFilePath = keystoreProperties.getProperty("storeFile")
        if (storeFilePath != null) {
            val candidate1 = file(storeFilePath)
            val candidate2 = rootProject.file(storeFilePath)
            if (candidate1.exists()) {
                resolvedKeyFile = candidate1
            } else if (candidate2.exists()) {
                resolvedKeyFile = candidate2
            }
        }
    }

    signingConfigs {
        create("release") {
            if (resolvedKeyFile != null && resolvedKeyFile.exists()) {
                keyAlias = keystoreProperties.getProperty("keyAlias")
                keyPassword = keystoreProperties.getProperty("keyPassword")
                storeFile = resolvedKeyFile
                storePassword = keystoreProperties.getProperty("storePassword")
                println(">>> Magaiees: Release signing config loaded successfully with keystore: ${resolvedKeyFile.absolutePath}")
            }
        }
    }

    buildTypes {
        release {
            if (resolvedKeyFile != null && resolvedKeyFile.exists()) {
                signingConfig = signingConfigs.getByName("release")
            } else {
                signingConfig = signingConfigs.getByName("debug")
            }
            isMinifyEnabled = true
            isShrinkResources = false
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }
    }

    lint {
        checkReleaseBuilds = false
        abortOnError = false
    }
}

dependencies {
    implementation("androidx.activity:activity-ktx:1.9.3")
    coreLibraryDesugaring("com.android.tools:desugar_jdk_libs:2.1.4")
}

flutter {
    source = "../.."
}
